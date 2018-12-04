<?php

namespace App\Repositories;

use App\Model\Setting;
use App\Repositories\Contracts\SettingRepository as SettingRepositoryInterface;

class SettingRepository implements SettingRepositoryInterface
{
    protected $model;

    public function __construct(Setting $model)
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

    public function update(Setting $sett, $data)
    {
        try{
            return $sett->update($data);
        }
        catch(\Exception $e){
           return $e->getMessage();
        }
    }

    public function delete(Setting $sett)
    {
        return $sett->delete();
    }

}
