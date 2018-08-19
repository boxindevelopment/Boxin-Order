<?php

namespace App\Repositories;

use App\Model\Box;
use App\Repositories\Contracts\BoxRepository as BoxRepositoryInterface;
use DB;

class BoxRepository implements BoxRepositoryInterface
{
    protected $model;

    public function __construct(Box $model)
    {
        $this->model = $model;
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
        return $this->model->get();
    }

    public function getData($args = [])
    {
        $query = $this->model->query();
        if(isset($args['orderColumns']) && isset($args['orderDir'])){
            $query->orderBy($args['orderColumns'], $args['orderDir']);
        }
        if(isset($args['status_id'])){
            $query->where('status_id', $args['status_id']);
        }
        if(isset($args['space_id'])){
            $query->where('space_id', $args['space_id']);
        }
        if(isset($args['types_of_size_id'])){
            $query->where('types_of_size_id', $args['types_of_size_id']);
        }
        if(isset($args['start'])){
            $query->skip($args['start']);
        }
        if(isset($args['length'])){
            $query->take($args['length']);
        }

        $query->where('deleted_at', NULL);
        $box = $query->get();

        return $box;

    }

    public function getBySpace($space_id)
    {
        $box = $this->model->select('types_of_size_id', DB::raw('COUNT(types_of_size_id) as available'))
                ->where('status_id', 10)
                ->where('space_id', $space_id)
                ->where('deleted_at', NULL)
                ->groupBy('types_of_size_id')
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
