<?php

namespace App\Repositories;

  use App\Model\Warehouse;
use App\Repositories\Contracts\WarehouseRepository as WarehouseRepositoryInterface;
use DB;

class WarehouseRepository implements WarehouseRepositoryInterface
{
    protected $model;

    public function __construct(Warehouse $model)
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

    public function getLatLong($space_id)
    {
        $data = $this->model->select('warehouses.lat', 'warehouses.long')
                ->leftJoin('spaces', 'warehouses.id','=','spaces.warehouse_id')
                ->where('spaces.id', $space_id)
                ->get();

        return $data;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(Warehouse $warehouse, $data)
    {
        try{
            return $warehouse->update($data);
        }
        catch(\Exception $e){
           return $e->getMessage();
        }
    }

    public function delete(Warehouse $warehouse)
    {
        return $warehouse->delete();
    }
}
