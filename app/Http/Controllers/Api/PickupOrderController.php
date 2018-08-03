<?php

namespace App\Http\Controllers\Api;

use App\Entities\TypePickup;
use App\Entities\Order;
use App\Entities\PickupOrder;
use App\Http\Controllers\Controller;
use App\Http\Resources\TypePickupResource;
use App\Http\Resources\PickupOrderResource;
use Illuminate\Http\Request;
use DB;

class PickupOrderController extends Controller
{
    public function getType(){

        $types = TypePickup::get();

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

    public function startPickUp(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'order_id'          => 'required',
            'type_pickup_id'    => 'required',
            'address'           => 'required',
            'date'              => 'required',
            'time'              => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        try {
            $data                   = $request->all();
            $pickup                 = new PickupOrder;
            $pickup->order_id       = $request->order_id;
            $pickup->type_pickup_id = $request->type_pickup_id;
            $pickup->address        = $request->address;
            $pickup->longitude      = $request->longitude;
            $pickup->latitude       = $request->latitude;
            $pickup->date           = $request->date;
            $pickup->time           = $request->time;
            $pickup->pickup_fee     = 0;
            $pickup->note           = $request->note;
            $pickup->save();
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data pickup order berhasil disimpan',
            'data' => new PickupOrderResource($pickup->fresh())
        ]);
    }

}