<?php

namespace App\Repositories;

use App\Model\Voucher;
use App\Model\AdminArea;
use App\Repositories\Contracts\VoucherRepository as VoucherRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use DB;

class VoucherRepository implements VoucherRepositoryInterface
{
    protected $model;

    public function __construct(Voucher $model)
    {
        $this->model = $model;
    }

    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    public function findByCode($code)
    {
        return $this->model->where('code', $code)->first();
    }

    public function all()
    {
        $now = date('Y-m-d');
        return $this->model->where('status_id', 20)
                           ->where('start_date', '>=', $now)
                           ->where('end_date', '<=', $now)
                           ->where('deleted_at', NULL)
                           ->orderBy('updated_at', 'DESC')
                           ->orderBy('id','DESC')
                           ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(Voucher $voucher, $data)
    {
        return $voucher->update($data);
    }

    public function delete(Voucher $voucher)
    {
        return $voucher->delete();
    }

}
