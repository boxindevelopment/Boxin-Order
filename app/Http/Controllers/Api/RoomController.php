<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Model\Room;
use App\Http\Resources\RoomResource;
use App\Repositories\Contracts\RoomRepository;

class RoomController extends Controller
{
    protected $rooms;

    public function __construct(RoomRepository $rooms)
    {
        $this->rooms = $rooms;
    }

    public function getRoomBySpace($space_id){

        $rooms = $this->rooms->getBySpace($space_id);

        if(count($rooms) > 0) {
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

}
