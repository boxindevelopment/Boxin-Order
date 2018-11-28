<?php

namespace App\Repositories\Contracts;

use App\Model\Category;

interface CategoryRepository
{
    public function create(array $data);

    public function update(Category $cat, $data);

    public function delete(Category $cat);
}
