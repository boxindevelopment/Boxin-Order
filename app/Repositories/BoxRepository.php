<?php

namespace App\Repositories;

use App\Model\Box;
use App\Repositories\Contracts\BoxRepository as BoxRepositoryInterface;
use DB;
use App\Model\Price;

class BoxRepository implements BoxRepositoryInterface
{
    protected $model;
    protected $price;

    public function __construct(Box $model, Price $price)
    {
        $this->model = $model;
        $this->price = $price;
    }

    public function findOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function all()
    {
        return $this->model->groupBy('types_of_size_id')->get();
    }

    public function getData($args = [])
    {
        $query = $this->model->query();
        $query->select('boxes.*');
        $query->leftJoin('shelves', 'shelves.id', '=', 'boxes.shelves_id');

        if(isset($args['orderColumns']) && isset($args['orderDir'])){
            $query->orderBy($args['orderColumns'], $args['orderDir']);
        }
        if(isset($args['status_id'])){
            $query->where('boxes.status_id', $args['status_id']);
        }
        if(isset($args['area_id'])){
            $query->where('shelves.area_id', $args['area_id']);
        }
        if(isset($args['types_of_size_id'])){
            $query->where('boxes.types_of_size_id', $args['types_of_size_id']);
        }
        if(isset($args['start'])){
            $query->skip($args['start']);
        }
        if(isset($args['length'])){
            $query->take($args['length']);
        }
        $query->where('boxes.deleted_at', NULL);
        $box = $query->get();

        return $box;

    }

    public function getAvailable($types_of_size_id, $city_id)
    {
        $box = $this->model->select('boxes.types_of_size_id', 'boxes.shelves_id', DB::raw('COUNT(boxes.types_of_size_id) as available'))
                ->leftJoin('shelves', 'shelves.id', '=', 'boxes.shelves_id')
                ->leftJoin('areas', 'areas.id', '=', 'shelves.area_id')
                ->where('boxes.status_id', 10)
                ->where('boxes.types_of_size_id', $types_of_size_id)
                ->where('areas.city_id', $city_id)
                ->where('boxes.deleted_at', NULL)
                ->where('areas.deleted_at', NULL)
                ->groupBy('boxes.types_of_size_id', 'shelves_id')
                ->get();
        return $box;
    }

    public function getByArea($area_id)
    {
        $box = $this->model->select('boxes.types_of_size_id', DB::raw('COUNT(boxes.types_of_size_id) as available'))
                ->leftJoin('shelves', 'shelves.id', '=', 'boxes.shelves_id')
                ->where('boxes.status_id', 10)
                ->where('shelves.area_id', $area_id)
                ->where('boxes.deleted_at', NULL)
                ->groupBy('boxes.types_of_size_id')
                ->get();

        return $box;
    }

    public function getBox($duration)
    {
        $box = $this->price->select('prices.*', DB::raw('prices.price as price'), DB::raw('types_of_size.name as size_name'), DB::raw('types_of_size.size as size'), DB::raw('types_of_duration.name as duration_name'), DB::raw('types_of_duration.alias as duration_alias'))
                ->leftJoin('types_of_size', 'types_of_size.id', '=', 'prices.types_of_size_id')
                ->leftJoin('types_of_duration', 'types_of_duration.id', '=', 'prices.types_of_duration_id')
                ->where('prices.types_of_box_room_id', 1)
                ->where('types_of_size.types_of_box_room_id', 1)
                ->where('types_of_duration.id', $duration)
                ->get();
        return $box;
    }

    public function findPaginate($args = [])
    {
        // Set default args
        $args = array_merge([
            'perPage' => 6,
            'sortBy' => null,
            'sortOrder' => null
        ], $args);

        $query = $this->model->with('categories', 'itemPrice.units')->select('items.*', DB::raw('(SELECT SUM(`in`) - SUM(`out`) as stock FROM `item_stocks` WHERE `item`=`items`.`id`) AS stock'));

        if (!in_array('category', $args)) {
            if (isset($args['category'])) {
                if($args['category']){
                    $query
                        ->leftJoin('categories', 'items.category', '=', 'categories.id');
                }
            }
        }

        if (isset($args['name'])) {
            if($args['name']){
                $query->where('name', 'like', '%'.$args['name'].'%');
            }
        }

        if (isset($args['populer'])) {
            if($args['populer']){
                $query->where('items.populer', $args['populer']);
            }
        }

        if (isset($args['categories'])) {
            if($args['categories']){
                $query->where('items.category', $args['categories']);
            }
        }

        if (isset($args['search'])) {
            if($args['search']){
                $query->whereRaw("(name LIKE '%".$args['search']."%' OR code LIKE '%".$args['search']."%')");
            }
        }

        if (isset($args['sortBy']) && isset($args['sortOrder'])) {
            if($args['sortBy'] && $args['sortOrder']){
                $query->orderBy($args['sortBy'], $args['sortOrder']);
            }
        }

        if (isset($args['random'])) {
            if($args['random']){
                $query->orderByRaw('RAND()');
            }
        }

        $boxs = $query->paginate($args['perPage']);

        return $boxs;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(Box $box, $data)
    {
        try{
            return $box->update($data);
        }
        catch(\Exception $e){
           return $e->getMessage();
        }
    }

    public function delete(Box $box)
    {
        return $box->delete();
    }
}
