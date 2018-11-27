<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TypeSizeResource;
use App\Repositories\Contracts\TypeSizeRepository;

class TypeSizeController extends Controller
{
    protected $size;

    public function __construct(TypeSizeRepository $size)
    {
        $this->size = $size;
    }

    public function list($types_of_box_room_id){

        $size = $this->size->all($types_of_box_room_id);

        if(count($size) > 0) {
            $data = TypeSizeResource::collection($size);

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
