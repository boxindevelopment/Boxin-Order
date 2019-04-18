<?php

namespace App\Repositories;

use App\Model\Space;
use App\Model\Shelves;
use App\Repositories\Contracts\SpaceRepository as SpaceRepositoryInterface;
use DB;

class SpaceRepository implements SpaceRepositoryInterface
{
    protected $model;
    protected $shelves;

    public function __construct(Space $model, Shelves $shelves)
    {
        $this->model    = $model;
        $this->shelves  = $shelves;
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
        $query->select('spaces.*');
        // $query->leftJoin('shelves', 'shelves.space_id', '=', 'spaces.id');
        // $query->leftJoin('boxes', 'boxes.shelves_id', '=', 'shelves.id');

        if(isset($args['orderColumns']) && isset($args['orderDir'])){
            $query->orderBy($args['orderColumns'], $args['orderDir']);
        }
        if(isset($args['status_id'])){
            $query->where('spaces.status_id', $args['status_id']);
        }
        if(isset($args['area_id'])){
            $query->where('spaces.area_id', $args['area_id']);
        }
        if(isset($args['types_of_size_id'])){
            $query->where('spaces.types_of_size_id', $args['types_of_size_id']);
        }
        if(isset($args['start'])){
            $query->skip($args['start']);
        }
        if(isset($args['length'])){
            $query->take($args['length']);
        }
        $query->where('spaces.deleted_at', NULL);
        $box = $query->get();

        return $box;

    }

    public function getByArea($area_id)
    {
        $room = $this->model->select('types_of_size_id', DB::raw('COUNT(types_of_size_id) as available'))
                ->where('status_id', 10)
                ->where('area_id', $area_id)
                ->where('deleted_at', NULL)
                ->groupBy('types_of_size_id')
                ->get();
        return $room;
    }
    
    public function anyBoxInSpace()
    {
        $shelf = $this->shelves->select('space_id')->get();
        $data  = $shelf->toArray();
        $room  = $this->model->select('spaces.*')
                ->leftJoin('shelves', 'shelves.space_id', '=', 'spaces.id')
                ->leftJoin('boxes', 'boxes.shelves_id', '=', 'shelves.id')
                ->whereNotIn('spaces.id', $data)
                ->where('boxes.deleted_at', NULL)
                ->get();
        return $room;
    }

    public function getAvailable($types_of_size_id, $city_id)
    {
        $shelf = $this->shelves->select('space_id')->get();
        $data  = $shelf->toArray();
        $room  = $this->model->select('spaces.types_of_size_id', 'spaces.area_id', DB::raw('COUNT(spaces.types_of_size_id) as available'))
                ->leftJoin('shelves', 'shelves.space_id', '=', 'spaces.id')
                ->leftJoin('boxes', 'boxes.shelves_id', '=', 'shelves.id')
                ->leftJoin('areas', 'areas.id', '=', 'spaces.area_id')
                ->whereNotIn('spaces.id', $data)
                ->where('spaces.status_id', 10)
                ->where('spaces.types_of_size_id', $types_of_size_id)
                ->where('areas.city_id', $city_id)
                ->where('spaces.deleted_at', NULL)
                ->where('areas.deleted_at', NULL)
                ->groupBy('spaces.types_of_size_id', 'spaces.area_id')
                ->get();
        return $room;
    }

    public function getAvailableByArea($area_id, $types_of_size_id)
    {
        $room  = $this->model->select('spaces.*')
                ->where('spaces.status_id', 10)
                ->where('spaces.types_of_size_id', $types_of_size_id)
                ->where('spaces.area_id', $area_id)
                ->where('spaces.deleted_at', NULL)
                ->first();
        return $room;
    }

    public function findPaginate($args = [])
    {
        // Set default args
        $args = array_merge([
            'perPage' => 6,
            'sortBy' => null,
            'sortOrder' => null
        ], $args);

        $query = $this->model->query();


        if (isset($args['name'])) {
            if($args['name']){
                $query->where('name', 'like', '%'.$args['name'].'%');
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

        $rooms = $query->paginate($args['perPage']);

        return $rooms;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(Space $space, $data)
    {
        try{
            return $space->update($data);
        }
        catch(\Exception $e){
           return $e->getMessage();
        }
    }

    public function delete(Space $space)
    {
        return $space->delete();
    }
}
