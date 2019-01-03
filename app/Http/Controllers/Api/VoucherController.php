<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\VoucherResource;
use App\Repositories\Contracts\VoucherRepository;

class VoucherController extends Controller
{
    protected $repository;

    public function __construct(VoucherRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        $voucher = $this->repository->all();

        if(count($voucher) > 0) {
            $data = VoucherResource::collection($voucher);

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data not found'
        ]);

    }

    public function detail($code)
    {
        $voucher = $this->repository->findByCode($code);
        if($voucher){
            $data = new VoucherResource($voucher);
            if($data->status->name != 'Actived'){
                return response()->json([
                    'status' => false,
                    'message' => 'voucher is not actived'
                ]);
            } else if(strtotime($data->start_date) > strtotime(date('Y-m-d'))){
                return response()->json([
                    'status' => false,
                    'message' => 'voucher is not valid '
                ]);
            } else if(strtotime($data->end_date) < strtotime(date('Y-m-d'))){
                return response()->json([
                    'status' => false,
                    'message' => 'voucher is expired'
                ]);
            }
            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data not found'
        ]);
    }

}
