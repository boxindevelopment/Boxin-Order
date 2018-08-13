<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Model\Box;
use App\Http\Resources\BoxResource;

class BoxController extends Controller
{

    public function getBoxBySpace($space_id){

        $boxes = Box::select('*', DB::raw('COUNT(types_of_size_id) as available'))
                ->where('status_id', 9)
                ->where('space_id', $space_id)
                ->groupBy('types_of_size_id')
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

    
}