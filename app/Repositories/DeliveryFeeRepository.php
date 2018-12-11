<?php

namespace App\Repositories;

  use App\Model\DeliveryFee;
use App\Repositories\Contracts\DeliveryFeeRepository as DeliveryFeeRepositoryInterface;
use DB;

class DeliveryFeeRepository implements DeliveryFeeRepositoryInterface
{
    protected $model;

    public function __construct(DeliveryFee $model)
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

    public function getFee($area_id)
    {
        return $this->model->where('area_id', $area_id)->first();
    }

    public function minFee()
    {
        return $this->model->orderBy('fee', 'ASC')->first();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(DeliveryFee $fee, $data)
    {
        try{
            return $fee->update($data);
        }
        catch(\Exception $e){
           return $e->getMessage();
        }
    }

    public function delete(DeliveryFee $fee)
    {
        return $fee->delete();
    }
}
