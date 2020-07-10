<?php

namespace App\Repositories;

use App\Model\SpaceSmall;
use App\Repositories\Contracts\SpaceSmallRepository as SpaceSmallRepositoryInterface;
use DB;
use App\Model\Price;

class SpaceSmallRepository implements SpaceSmallRepositoryInterface
{
    protected $model;
    protected $price;

    public function __construct(SpaceSmall $model, Price $price)
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
        $query->select('space_smalls.*');
        $query->leftJoin('shelves', 'shelves.id', '=', 'space_smalls.shelves_id');

        if(isset($args['orderColumns']) && isset($args['orderDir'])){
            $query->orderBy($args['orderColumns'], $args['orderDir']);
        }
        if(isset($args['status_id'])){
            $query->where('space_smalls.status_id', $args['status_id']);
        }
        if(isset($args['area_id'])){
            $query->where('shelves.area_id', $args['area_id']);
        }
        if(isset($args['types_of_size_id'])){
            $query->where('space_smalls.types_of_size_id', $args['types_of_size_id']);
        }
        if(isset($args['start'])){
            $query->skip($args['start']);
        }
        if(isset($args['length'])){
            $query->take($args['length']);
        }
        $query->where('space_smalls.deleted_at', NULL);
        $spaceSmall = $query->get();

        return $spaceSmall;

    }

    public function getDataCount($args = [])
    {
        $query = $this->model->query();
        $query->leftJoin('shelves', 'shelves.id', '=', 'space_smalls.shelves_id');

        if(isset($args['status_id'])){
            $query->where('space_smalls.status_id', $args['status_id']);
        }
        if(isset($args['area_id'])){
            $query->where('shelves.area_id', $args['area_id']);
        }
        if(isset($args['types_of_size_id'])){
            $query->where('space_smalls.types_of_size_id', $args['types_of_size_id']);
        }
        $query->where('space_smalls.deleted_at', NULL);
        $count = $query->count();

        return $count;

    }

    public function getAvailable($types_of_size_id, $city_id)
    {
        $spaceSmall = $this->model->select('space_smalls.types_of_size_id', 'space_smalls.shelves_id', DB::raw('COUNT(space_smalls.types_of_size_id) as available'))
                ->leftJoin('shelves', 'shelves.id', '=', 'space_smalls.shelves_id')
                ->leftJoin('areas', 'areas.id', '=', 'shelves.area_id')
                ->where('space_smalls.status_id', 10)
                ->where('space_smalls.types_of_size_id', $types_of_size_id)
                ->where('areas.city_id', $city_id)
                ->where('space_smalls.deleted_at', NULL)
                ->where('areas.deleted_at', NULL)
                ->groupBy('space_smalls.types_of_size_id', 'shelves_id')
                ->get();
        return $spaceSmall;
    }

    public function getByArea($area_id)
    {
        $spaceSmall = $this->model->select('space_smalls.types_of_size_id', 'space_smalls.shelves_id', DB::raw('COUNT(space_smalls.types_of_size_id) as available'))
                ->leftJoin('shelves', 'shelves.id', '=', 'space_smalls.shelves_id')
                ->where('space_smalls.status_id', 10)
                ->where('shelves.area_id', $area_id)
                ->where('space_smalls.deleted_at', NULL)
                ->groupBy('space_smalls.types_of_size_id')
                ->groupBy('space_smalls.shelves_id')
                ->get();

        return $spaceSmall;
    }

    public function getSpaceSmall($duration)
    {
        $spaceSmall = $this->price->select('prices.*', DB::raw('prices.price as price'), DB::raw('types_of_size.name as size_name'), DB::raw('types_of_size.size as size'), DB::raw('types_of_duration.name as duration_name'), DB::raw('types_of_duration.alias as duration_alias'))
                ->leftJoin('types_of_size', 'types_of_size.id', '=', 'prices.types_of_size_id')
                ->leftJoin('types_of_duration', 'types_of_duration.id', '=', 'prices.types_of_duration_id')
                ->where('prices.types_of_box_room_id', 1)
                ->where('types_of_size.types_of_box_room_id', 1)
                ->where('types_of_duration.id', $duration)
                ->get();
        return $spaceSmall;
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

        $spaceSmalls = $query->paginate($args['perPage']);

        return $spaceSmalls;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(SpaceSmall $spaceSmall, $data)
    {
        try{
            return $spaceSmall->update($data);
        }
        catch(\Exception $e){
           return $e->getMessage();
        }
    }

    public function delete(SpaceSmall $spaceSmall)
    {
        return $spaceSmall->delete();
    }
}
