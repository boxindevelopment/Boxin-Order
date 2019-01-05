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

    public function detail($code, Request $request)
    {
        $voucher = $this->repository->findByCode($code);
        if($voucher){

            $validator = \Validator::make($request->all(), [
                'amount'       => 'required|numeric',
            ]);

            if($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()
                ]);
            }

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
            } else if($request->amount < $data->min_amount){
                return response()->json([
                    'status' => false,
                    'message' => 'Amount less than ' . $data->min_amount . ' (min amount)'
                ]);
            }
            if($data->type_voucher == 2){
                $result = $request->amount;
            } else {
                $result = ($data->value/100) * $request->amount;
                if($result > $data->max_value){
                    $result = $data->max_value;
                }
            }
            return response()->json([
                'status' => true,
                'data' => $data,
                'result' => $result
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data not found'
        ]);
    }

}
