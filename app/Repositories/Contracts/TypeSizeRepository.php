<?php

namespace App\Repositories\Contracts;

use App\Model\TypeSize;

interface TypeSizeRepository
{
    public function create(array $data);

    public function update(TypeSize $size, $data);

    public function delete(TypeSize $size);
}
