<?php

namespace App\Repositories\Contracts;

use App\Model\TypePickup;

interface TypePickupRepository
{
    public function create(array $data);

    public function update(TypePickup $type, $data);

    public function delete(TypePickup $type);
}
