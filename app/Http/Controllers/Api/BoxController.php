<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Entities\Box;
use App\Entities\PriceBox;
use App\Http\Resources\BoxResource;
use App\Http\Resources\PriceBoxResource;

class BoxController extends Controller
{

    public function getBoxBySpace($space_id){

        $boxes = Box::select('*', DB::raw('COUNT(type_size_id) as available'))
                ->where('status_id', 9)
                ->where('space_id', $space_id)
                ->groupBy('type_size_id')
                ->get();

        if(count($boxes) != 0) {
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

    public function getPriceBoxByTypeSize($type_size_id)
    {
        $boxes = PriceBox::where('type_size_id', $type_size_id)
                ->get();

        if(count($boxes) != 0) {
            $data = PriceBoxResource::collection($boxes);

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