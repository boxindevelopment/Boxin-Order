<?php

namespace App\Http\Controllers\Api;

use App\Model\TypePickup;
use App\Http\Controllers\Controller;
use App\Http\Resources\TypePickupResource;
use App\Repositories\Contracts\TypePickupRepository;

class TypePickupController extends Controller
{
    protected $repository;

    public function __construct(TypePickupRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getType()
    {
        $types = $this->repository->all();

        if(count($types) != 0) {
            $data = TypePickupResource::collection($types);

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
