<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\BoxResource;
use App\Http\Resources\PriceResource;
use App\Repositories\Contracts\BoxRepository;

class BoxController extends Controller
{
    protected $boxes;

    public function __construct(BoxRepository $boxes)
    {
        $this->boxes = $boxes;
    }

    public function listByArea($area_id)
    {
        $boxes = $this->boxes->getByArea($area_id);

        if(count($boxes) > 0) {
            $data = BoxResource::collection($boxes);

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

    public function getBox($duration)
    {
        $boxes = $this->boxes->getBox($duration);

        if(count($boxes) > 0) {
            $data =PriceResource::collection($boxes);

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
        $boxes = $this->boxes->getBoxPagination($params);

        if(count($boxes) > 0) {
            $data =PriceResource::collection($boxes);

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
