<?php

namespace App\Repositories;

  use App\Model\TypePickup;
use App\Repositories\Contracts\TypePickupRepository as TypePickupRepositoryInterface;
use DB;

class TypePickupRepository implements TypePickupRepositoryInterface
{
    protected $model;

    public function __construct(TypePickup $model)
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

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(TypePickup $type, $data)
    {
        try{
            return $type->update($data);
        }
        catch(\Exception $e){
           return $e->getMessage();
        }
    }

    public function delete(TypePickup $type)
    {
        return $type->delete();
    }
}
