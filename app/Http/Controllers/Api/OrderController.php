<?php

namespace App\Http\Controllers\Api;

use App\Model\Order;
use App\Model\Room;
use App\Model\Box;
use App\Model\OrderDetail;
use App\Model\Price;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\PriceResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class OrderController extends Controller
{
    public function chooseProduct(){
        $choose = Price::select('types_of_box_room.name', DB::raw('MIN(price) as min'), DB::raw('MAX(price) as max'), 'types_of_duration.alias')
            ->leftJoin('types_of_box_room', 'types_of_box_room.id', '=', 'prices.types_of_box_room_id')
            ->leftJoin('types_of_duration', 'types_of_duration.id', '=', 'prices.types_of_duration_id')
            ->where('prices.types_of_size_id', '4')
            ->groupBy('types_of_box_room.name')
            ->groupBy('types_of_duration.alias')
            ->get();

        $choose2 = Price::select('types_of_box_room.name', DB::raw('MIN(price) as min'), DB::raw('MAX(price) as max'), 'types_of_duration.alias')
            ->leftJoin('types_of_box_room', 'types_of_box_room.id', '=', 'prices.types_of_box_room_id')
            ->leftJoin('types_of_duration', 'types_of_duration.id', '=', 'prices.types_of_duration_id')
            ->where('prices.types_of_size_id', '1')
            ->groupBy('types_of_box_room.name')
            ->groupBy('types_of_duration.alias')
            ->get();

        $arr1           = array();
        $arr1['name']   = $choose[0]->name;
        $arr1['min']    = $choose[0]->min;
        $arr1['max']    = $choose[0]->max;
        $arr1['time']   = $choose[0]->alias;

        $arr2 = array();
        $arr2['name']   = $choose2[0]->name;
        $arr2['min']    = $choose2[0]->min;
        $arr2['max']    = $choose2[0]->max;
        $arr2['time']   = $choose2[0]->alias;

        if(count($choose) != 0) {

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

    public function getById($order_detail_id)
    {
        $orders = OrderDetail::select('order_details.*', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('datediff(order_details.end_date, order_details.start_date) as total_time'), DB::raw('datediff(current_date(),order_details.start_date) as selisih'))
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('order_details.id', $order_detail_id)
            ->get();

        if(count($orders) != 0) {
            $data = OrderDetailResource::collection($orders);

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

    public function getPrice($types_of_box_room_id, $types_of_size_id)
    {
        $prices = Price::where('types_of_box_room_id', $types_of_box_room_id)
                ->where('types_of_size_id', $types_of_size_id)
                ->get();

        if(count($prices) != 0) {
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

        $validator = \Validator::make($request->all(), [
            'user_id' => 'required',
            'space_id' => 'required',
            'order_count' => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        try {
            $data               = $request->all();
            $order              = new Order;
            $order->user_id     = $request->user_id;
            $order->space_id    = $request->space_id;
            $order->status_id   = 10;
            $order->qty         = $data['order_count'];
            $order->save();

            $total = 0;
            $total_amount = 0;
            if(isset($data['order_count'])) {
                for ($a = 1; $a <= $data['order_count']; $a++) {
                    $order_detail                         = new OrderDetail;
                    $order_detail->order_id               = $order->id;
                    $order_detail->status_id              = 10;
                    $order_detail->types_of_duration_id   = $data['types_of_duration_id'.$a];
                    $order_detail->types_of_box_room_id   = $data['types_of_box_room_id'.$a];
                    $order_detail->types_of_size_id       = $data['types_of_size_id'.$a];
                    $order_detail->duration               = $data['duration'.$a];
                    $order_detail->start_date             = Carbon::now()->toDateString();

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
                        $box = Box::where('status_id', 9)
                                ->where('space_id', $order->space_id)
                                ->where('types_of_size_id', $order_detail->types_of_size_id)
                                ->orderBy('id')
                                ->limit(1)
                                ->get();

                        if(isset($box)){
                            $room_or_box_id = $box[0]->id;
                            //change status box to fill
                            DB::table('boxes')->where('id', $room_or_box_id)->update(['status_id' => 8]);
                        }

                        // get price box
                        $price = Price::select('price')
                                ->where('types_of_box_room_id', $order_detail->types_of_box_room_id)
                                ->where('types_of_size_id', $order_detail->types_of_size_id)
                                ->get();

                        if(isset($price)){
                            $amount = ($price[0]->price)*$order_detail->duration;
                        }else{
                            // change status room to empty when order failed to create
                            DB::table('boxes')->where('id', $room_or_box_id)->update(['status_id' => 9]);
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
                        $room = Room::where('status_id', 9)
                                ->where('space_id', $order->space_id)
                                ->where('types_of_size_id', $order_detail->types_of_size_id)
                                ->orderBy('id')
                                ->limit(1)
                                ->get();

                        if(isset($room)){
                            $room_or_box_id = $room[0]->id;
                            //change status room to fill
                            DB::table('rooms')->where('id', $room_or_box_id)->update(['status_id' => 8]);
                        }

                        // get price room
                        $price = Price::select('price')
                                ->where('types_of_box_room_id', $order_detail->types_of_box_room_id)
                                ->where('types_of_size_id', $order_detail->types_of_size_id)
                                ->get();

                        if(isset($price)){
                            $amount = ($price[0]->price)*$order_detail->duration;
                        }else{
                            // change status room to empty when order failed to create
                            DB::table('rooms')->where('id', $room_or_box_id)->update(['status_id' => 9]);
                            return response()->json([
                                'status' =>false,
                                'message' => 'Not found price room.'
                            ]);
                        }
                    }

                    $order_detail->name           = 'New '. $type .' '. $a;
                    $order_detail->room_or_box_id = $room_or_box_id;
                    $order_detail->amount         = $amount;

                    $total += $order_detail->amount;
                    $order_detail->save();
                }
                //update total order
                $total_amount += $total;
                DB::table('orders')->where('id', $order->id)->update(['total' => $total_amount]);

            } else {
                return response()->json([
                    'status' =>false,
                    'message' => 'Not found order count.'
                ]);
            }

        } catch (\Exception $e) {
            // delete order when order_detail failed to create
            DB::table('orders')->where('id', $order->id)->delete();
            return response()->json([
                'status' =>false,
                'message' => $e->getMessage()
            ]);
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
