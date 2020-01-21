<?php

namespace App\Repositories\Contracts;

use App\Model\TransactionLog;

interface TransactionLogRepository
{
    public function create(array $data);

    public function update(TransactionLog $transactionLog, $data);

    public function delete(TransactionLog $transactionLog);
}
