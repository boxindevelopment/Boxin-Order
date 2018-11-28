<?php

namespace App\Repositories\Contracts;

use App\Model\DeliveryFee;

interface DeliveryFeeRepository
{
    public function create(array $data);

    public function update(DeliveryFee $fee, $data);

    public function delete(DeliveryFee $fee);
}
