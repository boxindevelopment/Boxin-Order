<?php

namespace App\Repositories;

  use App\Model\TypeSize;
use App\Repositories\Contracts\TypeSizeRepository as TypeSizeRepositoryInterface;
use DB;

class TypeSizeRepository implements TypeSizeRepositoryInterface
{
    protected $model;

    public function __construct(TypeSize $model)
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

    public function all($types_of_box_room_id)
    {
        return $this->model->where('types_of_box_room_id', $types_of_box_room_id)->get();
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

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(TypeSize $size, $data)
    {
        try{
            return $size->update($data);
        }
        catch(\Exception $e){
           return $e->getMessage();
        }
    }

    public function delete(TypeSize $size)
    {
        return $size->delete();
    }
}
