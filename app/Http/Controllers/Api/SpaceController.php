<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\SpaceSmallResource;
use App\Repositories\Contracts\SpaceSmallRepository;

class SpaceController extends Controller
{
    protected $space;

    public function __construct(SpaceSmallRepository $space)
    {
        $this->space = $space;
    }

    public function listByArea($area_id)
    {

        $spaces = $this->space->getByArea($area_id);

        if(count($spaces) > 0) {
            $data = SpaceSmallResource::collection($spaces);

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
