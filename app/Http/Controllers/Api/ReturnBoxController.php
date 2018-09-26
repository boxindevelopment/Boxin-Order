<?php

namespace App\Http\Controllers\Api;

use App\Model\ReturnBoxes;
use App\Model\Order;
use App\Model\Room;
use App\Model\Box;
use App\Model\OrderDetail;
use App\Model\Price;
use App\Model\PickupOrder;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReturnBoxesResource;
use Illuminate\Http\Request;
use DB;
use App\Model\Setting;
use App\Repositories\Contracts\WarehouseRepository;

class ReturnBoxController extends Controller
{
    protected $warehouse;

    public function __construct(WarehouseRepository $warehouse)
    {
        $this->warehouse = $warehouse;
    }

    public function getPrice(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'order_detail_id'   => 'required',
            'longitude'         => 'required',
            'latitude'          => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }
        //get space_id
        $order = Order::select('orders.*')->leftJoin('order_details', 'order_details.order_id', '=', 'orders.id')->where('order_details.id', $request->order_detail_id)->first();

        //get price
        $price      = Setting::where('name', 'like', '%price_distance%')->first();
        $price      = $price->value;

        //get lat long warehouse
        $warehouse = $this->warehouse->getLatLong($order->space_id);
        
        $latitude1  = $request->latitude;
        $longitude1 = $request->longitude;
        $latitude2  = $warehouse[0]->lat;
        $longitude2 = $warehouse[0]->long;

        // <cara 1>
        $theta    = $longitude1 - $longitude2;
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2)))  + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515; //Mi
        // $distance = $distance * 1.609344; //Km
        $deliver_fee = round($distance,2) * $price;
        // <end cara 1>

        return response()->json([
            'status' => true,
            'price'  => $deliver_fee
        ]);

    }

    public function startReturnBox(Request $request)
    {

        $user = $request->user();
        
        $validator = \Validator::make($request->all(), [
            'order_detail_id'   => 'required',
            'types_of_pickup_id'=> 'required',
            'date'              => 'required',
            'time'              => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        $data = $request->all();

        try {

            $return                         = new ReturnBoxes;
            $return->types_of_pickup_id     = $data['types_of_pickup_id'];
            $return->date                   = $data['date'];
            $return->time                   = $data['time'];
            $return->note                   = $data['note'];
            $return->status_id              = 11;
            $return->address                = $data['address'];
            $return->order_detail_id        = $data['order_detail_id'];
            $return->longitude              = $data['longitude'];
            $return->latitude               = $data['latitude'];        
            $return->deliver_fee            = $data['deliver_fee'];
            $return->save();

            //update status order detail to
            DB::table('order_details')->where('id', $return->order_detail_id)->update(['status_id' => 11]);

        } catch (\Exception $e) {
            return response()->json([
                'status' =>false,
                'message' => $e->getMessage()
            ], 401);
        }

        return response()->json([
            'status' => true,
            'message' => 'Create return box success.',
            'data' => new ReturnBoxesResource($return)
        ]);

    }

    public function update(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'order_detail_id'   => 'required',
            'name'              => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        try {
            $id         = $request->order_detail_id;
            $order      = OrderDetail::findOrFail($id);
            $data       = $request->all();
            if($order){
                $data["name"]           = $request->name;
                $order->fill($data)->save();
            }

        } catch (\Exception $e) {

            return response()->json([
                'status' =>false,
                'message' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Update name order detail success.',
            'data' => $order
        ]);

    }

}
