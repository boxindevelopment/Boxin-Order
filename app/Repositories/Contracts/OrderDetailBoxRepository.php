<?php

namespace App\Repositories\Contracts;

use App\Model\OrderDetailBox;

interface OrderDetailBoxRepository
{
    public function create(array $data);

    public function update(OrderDetailBox $orderDetailBox, $data);

    public function delete(OrderDetailBox $orderDetailBox);
}
