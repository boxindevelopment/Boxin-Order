<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PriceResource;
use App\Repositories\Contracts\PriceRepository;

class PriceController extends Controller
{
    protected $repository;

    public function __construct(PriceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listPriceArea($types_of_box_room_id, $types_of_size_id, $area_id){

        $price = $this->repository->getPriceArea($types_of_box_room_id, $types_of_size_id, $area_id);

        if(count($price) > 0) {
            $data = PriceResource::collection($price);

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
