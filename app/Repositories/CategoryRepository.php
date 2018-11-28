<?php

namespace App\Repositories;

  use App\Model\Category;
use App\Repositories\Contracts\CategoryRepository as CategoryRepositoryInterface;
use DB;

class CategoryRepository implements CategoryRepositoryInterface
{
    protected $model;

    public function __construct(Category $model)
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

    public function update(Category $cat, $data)
    {
        try{
            return $cat->update($data);
        }
        catch(\Exception $e){
           return $e->getMessage();
        }
    }

    public function delete(Category $cat)
    {
        return $cat->delete();
    }
}
