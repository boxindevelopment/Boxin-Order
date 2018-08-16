<?php

namespace App\Repositories;

  use App\Model\Room;
use App\Repositories\Contracts\RoomRepository as RoomRepositoryInterface;
use DB;

class RoomRepository implements RoomRepositoryInterface
{
    protected $model;

    public function __construct(Room $model)
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

        $room = $query->get();

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

    public function update(Room $room, $data)
    {
        try{
            return $room->update($data);
        }
        catch(\Exception $e){
           return $e->getMessage();
        }
    }

    public function delete(Room $room)
    {
        return $room->delete();
    }
}
