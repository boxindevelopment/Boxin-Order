<?php

namespace App\Http\Controllers\Api;

use App\Model\TypePickup;
use App\Model\Order;
use App\Model\PickupOrder;
use App\Model\Warehouse;
use App\Model\Setting;
use App\Http\Controllers\Controller;
use App\Http\Resources\TypePickupResource;
use App\Http\Resources\PickupOrderResource;
use Illuminate\Http\Request;
use App\Repositories\Contracts\WarehouseRepository;

class PickupOrderController extends Controller
{
    protected $warehouse;

    public function __construct(WarehouseRepository $warehouse)
    {
        $this->warehouse = $warehouse;
    }

    public function getType()
    {

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

    public function getPrice(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'space_id'          => 'required',
            'longitude'         => 'required',
            'latitude'          => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        //get price
        $price      = Setting::where('name', 'like', '%price_distance%')->get();
        $price      = $price[0]->value;

        //get lat long warehouse
        $warehouse = $this->warehouse->getLatLong($request->space_id);
        
        $latitude1  = $warehouse[0]->lat;
        $longitude1 = $warehouse[0]->long;
        $latitude2  = $request->latitude;
        $longitude2 = $request->longitude;

        // <cara 1>
        $theta    = $longitude1 - $longitude2;
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2)))  + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515; //Mi
        // $distance = $distance * 1.609344; //Km
        $pickup_fee = round($distance,2) * $price;
        // <end cara 1>

        return response()->json([
            'status' => true,
            'price' => $pickup_fee
        ]);

    }


}
