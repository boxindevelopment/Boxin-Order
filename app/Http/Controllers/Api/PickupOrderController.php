<?php

namespace App\Http\Controllers\Api;

use App\Model\TypePickup;
use App\Model\Order;
use App\Model\PickupOrder;
use App\Model\Setting;
use App\Http\Controllers\Controller;
use App\Http\Resources\TypePickupResource;
use App\Http\Resources\PickupOrderResource;
use Illuminate\Http\Request;

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
            'types_of_pickup_id'=> 'required',
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
            $pickup->types_of_pickup_id = $request->types_of_pickup_id;
            $pickup->address        = $request->address;
            $pickup->longitude      = $request->longitude;
            $pickup->latitude       = $request->latitude;
            $pickup->date           = $request->date;
            $pickup->time           = $request->time;
            $pickup->note           = $request->note;

            //get price
            $price      = Setting::where('name', 'like', '%price_distance%')->get();
            $price      = $price[0]->value;

            //get lat long warehouse
            $warehouse = Order::select('warehouses.lat', 'warehouses.long')
                        ->leftJoin('spaces', 'spaces.id','=','orders.space_id')
                        ->leftJoin('warehouses', 'warehouses.id','=','spaces.warehouse_id')
                        ->where('orders.id', $request->order_id)
                        ->get();
            $latitude1  = $warehouse[0]->lat;
            $longitude1 = $warehouse[0]->long;
            $latitude2  = $pickup->latitude;
            $longitude2 = $pickup->longitude;

            if($pickup->types_of_pickup_id == '1' || $pickup->types_of_pickup_id == 1){
                // <cara 1>
                $theta    = $longitude1 - $longitude2; 
                $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2)))  + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta))); 
                $distance = acos($distance); 
                $distance = rad2deg($distance); 
                $distance = $distance * 60 * 1.1515; //Mi
                // $distance = $distance * 1.609344; //Km
                $pickup_fee = round($distance,2) * $price; 
                // <end cara 1>

                // <cara 2>
                // $coord_a = $latitude1.','.$longitude1; 
                // $coord_b = $latitude2.','.$longitude2; 
                // $R = 6371;
                // $coord_a = explode(",",$coord_a);
                // $coord_b = explode(",",$coord_b);
                // $dLat = ($coord_b[0]) - ($coord_a[0]);
                // $dLat = $dLat * M_PI / 180;
                // $dLong = ($coord_b[1] - $coord_a[1]);
                // $dLong = $dLong * M_PI / 180;
                // $a = sin($dLat/2) * sin($dLat/2) + cos(($coord_a[0])* M_PI / 180) * cos(($coord_b[0])* M_PI / 180) * sin($dLong/2) * sin($dLong/2);
                // $c = 2 * atan2(sqrt($a), sqrt(1-$a));
                // $d = $R * $c;
                // $fee = $d * $price;
                // # hasil akhir dalam satuan kilometer
                // $pickup_fee = number_format($fee, 0, '.', ',');
                // <end cara 2>
                $pickup_fee = $pickup_fee;                
            }else{
                $pickup_fee = 0;
            }
            $pickup->pickup_fee     = $pickup_fee;
            $pickup->save();
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Create data pickup order success.',
            'data' => new PickupOrderResource($pickup->fresh())
        ]);
    }


}