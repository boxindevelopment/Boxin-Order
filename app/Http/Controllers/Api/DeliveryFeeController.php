<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryFeeResource;
use App\Repositories\Contracts\DeliveryFeeRepository;

class DeliveryFeeController extends Controller
{
    protected $repository;

    public function __construct(DeliveryFeeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function deliveryFee($area_id){

        $fee = $this->repository->getFee($area_id);
        $arr            = array();
        $arr['id']      = $fee->id;
        $arr['fee']     = intval($fee->fee);
        $arr['area_id'] = intval($fee->area_id);

        if($arr) {
            return response()->json([
                'status'    => true,
                'data'      => $arr,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data not found'
        ]);

    }

    public function minFee(){
        $fee = $this->repository->minFee();
        $arr            = array();
        $arr['id']      = $fee->id;
        $arr['fee']     = intval($fee->fee);

        if($arr) {
            return response()->json(['status' => true, 'data' => $arr,]);
        }

        return response()->json(['status' => false, 'message' => 'Data not found']);
    }

}
