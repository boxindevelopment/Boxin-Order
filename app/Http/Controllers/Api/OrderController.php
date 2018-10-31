<?php

namespace App\Http\Controllers\Api;

use App\Model\Order;
use App\Model\Room;
use App\Model\Box;
use App\Model\OrderDetail;
use App\Model\Price;
use App\Model\PickupOrder;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PriceResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Repositories\Contracts\BoxRepository;
use App\Repositories\Contracts\RoomRepository;
use App\Repositories\Contracts\PriceRepository;
use DB;

class OrderController extends Controller
{
    protected $rooms;
    protected $boxes;
    protected $price;

    public function __construct(BoxRepository $boxes, RoomRepository $rooms, PriceRepository $price)
    {
        $this->boxes = $boxes;
        $this->rooms = $rooms;
        $this->price = $price;
    }

    public function chooseProduct(){
        $choose1 = $this->price->getChooseProduct(1,1);
        $choose2 = $this->price->getChooseProduct(2,1);

        $arr1           = array();
        $arr1['name']   = $choose1->name;
        $arr1['min']    = $choose1->min;
        $arr1['max']    = $choose1->max;
        $arr1['time']   = $choose1->alias;

        $arr2 = array();
        $arr2['name']   = $choose2->name;
        $arr2['min']    = $choose2->min;
        $arr2['max']    = $choose2->max;
        $arr2['time']   = $choose2->alias;

        if(($choose1)) {

            return response()->json([
                'status'    => true,
                'data_box'  => $arr1,
                'data_room' => $arr2
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data not found'
        ]);
    }

    public function getOrder($id)
    {
        $order = Order::find($id);

        if($order){
            return response()->json([
                'status' => true,
                'data' => new OrderResource($order)
            ]);
        }


        return response()->json([
            'status' => false,
            'message' => 'Data not found'
        ]);

    }


    public function getPriceCity($types_of_box_room_id, $types_of_size_id, $city_id)
    {
        $prices = $this->price->getPriceCity($types_of_box_room_id, $types_of_size_id, $city_id);

        if(count($prices) > 0) {
            $data = PriceResource::collection($prices);

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


    public function startStoring(Request $request)
    {

        $user = $request->user();

        $validator = \Validator::make($request->all(), [
            'space_id' => 'required',
            'order_count' => 'required',
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
        if(isset($data['order_count'])) {
            for ($a = 1; $a <= $data['order_count']; $a++) {

                $validator = \Validator::make($request->all(), [
                    'types_of_size_id'.$a => 'required',
                    'types_of_box_room_id'.$a => 'required',
                    'types_of_duration_id'.$a => 'required',
                    'duration'.$a => 'required',
                ]);

                if($validator->fails()) {
                    return response()->json([
                        'status' => false,
                        'message' => $validator->errors()
                    ]);
                }
            }
        } else {
            return response()->json([
                'status' =>false,
                'message' => 'Not found order count.'
            ], 401);
        }

        try {
            $order              = new Order;
            $order->user_id     = $user->id;
            $order->space_id    = $request->space_id;
            $order->status_id   = 11;
            $order->total       = 0;
            $order->qty         = $data['order_count'];
            $order->save();

            $pickup                 = new PickupOrder;
            $pickup->date           = $request->date;

            $amount = 0;
            $total = 0;
            $total_amount = 0;

            for ($a = 1; $a <= $data['order_count']; $a++) {
                $order_detail                         = new OrderDetail;
                $order_detail->order_id               = $order->id;
                $order_detail->status_id              = 11;
                $order_detail->types_of_duration_id   = $data['types_of_duration_id'.$a];
                $order_detail->types_of_box_room_id   = $data['types_of_box_room_id'.$a];
                $order_detail->types_of_size_id       = $data['types_of_size_id'.$a];
                $order_detail->duration               = $data['duration'.$a];
                $order_detail->start_date             = $pickup->date;

                // daily
                if ($order_detail->types_of_duration_id == 1 || $order_detail->types_of_duration_id == '1') {
                    $order_detail->end_date     = date('Y-m-d', strtotime('+'.$order_detail->duration.' days', strtotime($order_detail->start_date)));

                }
                // weekly
                else if ($order_detail->types_of_duration_id == 2 || $order_detail->types_of_duration_id == '2') {
                    $end_date                   = $order_detail->duration*7;
                    $order_detail->end_date     = date('Y-m-d', strtotime('+'.$end_date.' days', strtotime($order_detail->start_date)));
                }
                // monthly
                else if ($order_detail->types_of_duration_id == 3 || $order_detail->types_of_duration_id == '3') {
                    $order_detail->end_date     = date('Y-m-d', strtotime('+'.$order_detail->duration.' month', strtotime($order_detail->start_date)));
                }


                // order box
                if ($order_detail->types_of_box_room_id == 1 || $order_detail->types_of_box_room_id == "1") {
                    $type = 'box';

                    // get box
                    $boxes = $this->boxes->getData(['status_id' => 10, 'space_id' => $request->space_id, 'types_of_size_id' => $data['types_of_size_id'.$a]]);

                    if(isset($boxes[0]->id)){
                        $room_or_box_id = $boxes[0]->id;
                        //change status box to fill
                        DB::table('boxes')->where('id', $room_or_box_id)->update(['status_id' => 9]);
                    }

                    // get price box
                    $price = $this->price->getPrice($order_detail->types_of_box_room_id, $order_detail->types_of_size_id, $order_detail->types_of_duration_id);

                    if($price){
                        $amount = $price->price*$order_detail->duration;
                    }else{
                        // change status room to empty when order failed to create
                        DB::table('boxes')->where('id', $room_or_box_id)->update(['status_id' => 10]);
                        return response()->json([
                            'status' =>false,
                            'message' => 'Not found price box.'
                        ]);
                    }
                }


                // order room
                if ($order_detail->types_of_box_room_id == 2 || $order_detail->types_of_box_room_id == "2") {
                    $type = 'room';
                    // get room
                    $rooms = $this->rooms->getData(['status_id' => 10, 'space_id' => $request->space_id, 'types_of_size_id' => $data['types_of_size_id'.$a]]);

                    if(isset($rooms[0]->id)){
                        $room_or_box_id = $rooms[0]->id;
                        //change status room to fill
                        DB::table('rooms')->where('id', $room_or_box_id)->update(['status_id' => 9]);
                    }

                    // get price room
                    $price = $this->price->getPrice($order_detail->types_of_box_room_id, $order_detail->types_of_size_id, $order_detail->types_of_duration_id);

                    if($price){
                        $amount = $price->price*$order_detail->duration;
                    }else{
                        // change status room to empty when order failed to create
                        DB::table('rooms')->where('id', $room_or_box_id)->update(['status_id' => 10]);
                        return response()->json([
                            'status' =>false,
                            'message' => 'Not found price room.'
                        ], 401);
                    }
                }

                $order_detail->name           = 'New '. $type .' '. $a;
                $order_detail->room_or_box_id = $room_or_box_id;
                $order_detail->amount         = $amount;

                $total += $order_detail->amount;
                $order_detail->save();
            }

            
            $pickup->order_id       = $order->id;
            $pickup->types_of_pickup_id = $request->types_of_pickup_id;
            $pickup->address        = $request->address;
            $pickup->longitude      = $request->longitude;
            $pickup->latitude       = $request->latitude;            
            $pickup->time           = $request->time;
            $pickup->time_pickup    = $request->time_pickup;
            $pickup->note           = $request->note;
            $pickup->pickup_fee     = 0;
            $pickup->status_id      = 11;
            $pickup->save();

            //update total order
            $total_amount += $total;
            DB::table('orders')->where('id', $order->id)->update(['total' => $total_amount]);

        } catch (\Exception $e) {
            // delete order when order_detail failed to create
            DB::table('orders')->where('id', $order->id)->delete();
            return response()->json([
                'status' =>false,
                'message' => $e->getMessage()
            ], 401);
        }

        return response()->json([
            'status' => true,
            'message' => 'Create order success.',
            'data' => new OrderResource($order)
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
