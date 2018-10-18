<?php

namespace App\Repositories\Contracts;

use App\Model\ReturnBoxes;

interface ReturnBoxRepository
{
    public function create(array $data);

    public function update(ReturnBoxes $returnboxes, $data);

    public function delete(ReturnBoxes $returnboxes);
}
