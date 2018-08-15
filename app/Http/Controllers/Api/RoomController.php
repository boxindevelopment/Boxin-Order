<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Model\Room;
use App\Model\PriceRoom;
use App\Http\Resources\RoomResource;
use App\Http\Resources\PriceRoomResource;

class RoomController extends Controller
{

    public function getRoomBySpace($space_id){

        $rooms = Room::select('types_of_size_id', DB::raw('COUNT(types_of_size_id) as available'))
                ->where('status_id', 9)
                ->where('space_id', $space_id)
                ->groupBy('types_of_size_id')
                ->get();

        if(count($rooms) != 0) {
            $data = RoomResource::collection($rooms);

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

    public function getPriceRoomByTypeSize($type_size_id)
    {
        $rooms = PriceRoom::where('type_size_id', $type_size_id)
                ->get();

        if(count($rooms) != 0) {
            $data = PriceRoomResource::collection($rooms);

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
