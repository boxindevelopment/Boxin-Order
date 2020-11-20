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


    public function getRoom($duration)
    {
        $spaces = $this->space->getSpaceSmall($duration);

        if(count($spaces) > 0) {
            $data =PriceResource::collection($spaces);

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

    public function getPagination(Request $request)
    {
        $params = array();
        $params['user_id'] = $user->id;
        $params['limit']   = intval($request->limit);
        $params['status_disable']   = 14;
        $params['search'] = ($request->search) ? $request->search : '';
        $spaces = $this->space->findPaginate($params);

        if(count($spaces) > 0) {
            $data =PriceResource::collection($spaces);

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
