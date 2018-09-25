<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Model\Box;
use App\Model\TypeSize;
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

    public function getBoxBySpace($space_id){

        $boxes = $this->boxes->getBySpace($space_id); 
        
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

    public function getBox($duration){

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


}
