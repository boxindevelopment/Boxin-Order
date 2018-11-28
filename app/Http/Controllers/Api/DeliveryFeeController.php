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

        if(count($fee) > 0) {
            $data = DeliveryFeeResource::collection($fee);

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
