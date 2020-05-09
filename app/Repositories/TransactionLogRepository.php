<?php

namespace App\Repositories;

use App\Model\TransactionLog;
use App\Repositories\Contracts\TransactionLogRepository as TransactionLogRepositoryInterface;
use DB;

class TransactionLogRepository implements TransactionLogRepositoryInterface
{
    protected $model;

    public function __construct(TransactionLog $model)
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

    public function findPaginate($args = [])
    {
        $args = array_merge([
            'perPage' => $args['limit'] != 0 ? $args['limit'] : 10,
        ], $args);

        $query = $this->model->query();
        $query->select('transaction_logs.*');
        $query->where('user_id', $args['user_id']);
        $query->orderBy('transaction_logs.created_at', 'DESC');
        $query->orderBy('transaction_logs.id', 'DESC');

        $orders = $query->paginate($args['perPage']);

        return $orders;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(TransactionLog $transactionLog, $data)
    {
        try{
            return $transactionLog->update($data);
        }
        catch(\Exception $e){
           return $e->getMessage();
        }
    }

    public function delete(TransactionLog $transactionLog)
    {
        return $transactionLog->delete();
    }
}
