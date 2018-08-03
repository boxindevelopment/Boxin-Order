<?php

namespace App\Http\Controllers\Api;

use App\Entities\Order;
use App\Entities\Room;
use App\Entities\Box;
use App\Entities\OrderDetail;
use App\Entities\PriceBox;
use App\Entities\PriceRoom;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderDetailResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class OrderController extends Controller
{
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

    public function my_box($user_id)
    {
        $orders = OrderDetail::select('order_details.*', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('datediff(order_details.end_date, order_details.start_date) as total_time'), DB::raw('datediff(current_date(),order_details.start_date) as selisih'))
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('user_id', $user_id)
            ->where('status_id', '=', 3)
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

    public function my_box_history($user_id)
    {
        $orders = OrderDetail::select('order_details.*', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('datediff(order_details.end_date, order_details.start_date) as total_time'), DB::raw('datediff(current_date(),order_details.start_date) as selisih'))
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('user_id', $user_id)
            ->where('status_id', '=', 11)
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

    public function my_deliveries($user_id)
    {
        $orders = OrderDetail::select('order_details.*', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('datediff(order_details.end_date, order_details.start_date) as total_time'), DB::raw('datediff(current_date(),order_details.start_date) as selisih'))
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('user_id', $user_id)
            ->where('status_id', '=', 1)
            ->orWhere('status_id', '=', 2)
            ->orWhere('status_id', '=', 10)
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
                    $order_detail           = new OrderDetail;
                    $order_detail->order_id = $order->id;
                    $order_detail->type_duration_id       = $data['type_duration_id'.$a];
                    $order_detail->type                   = $data['type'.$a];
                    $order_detail->type_size_id           = $data['type_size_id'.$a];
                    $order_detail->duration               = $data['duration'.$a];
                    $order_detail->name                   = 'New '. $data['type'.$a] .' '. $a;
                    $order_detail->start_date             = Carbon::now()->toDateString();
                    
                    // daily
                    if ($order_detail->type_duration_id == 1 || $order_detail->type_duration_id == '1') { 
                        $order_detail->end_date               = date('Y-m-d', strtotime('+'.$order_detail->duration.' days', strtotime($order_detail->start_date)));

                    } 
                    // weekly
                    else if ($order_detail->type_duration_id == 2 || $order_detail->type_duration_id == '2') { 
                        $end_date                             = $order_detail->duration*7;
                        $order_detail->end_date               = date('Y-m-d', strtotime('+'.$end_date.' days', strtotime($order_detail->start_date)));
                    } 
                    // monthly
                    else if ($order_detail->type_duration_id == 3 || $order_detail->type_duration_id == '3') { 
                        $order_detail->end_date               = date('Y-m-d', strtotime('+'.$order_detail->duration.' month', strtotime($order_detail->start_date)));
                    }

                    // order room
                    if ($order_detail->type == 'room') {
                        // get room
                        $room = Room::where('status_id', 9)
                                ->where('space_id', $order->space_id)
                                ->where('type_size_id', $order_detail->type_size_id)
                                ->orderBy('id')
                                ->limit(1)
                                ->get();
                        if(isset($room)){
                            $room_or_box_id = $room[0]->id;
                            //change status room to fill
                            DB::table('rooms')->where('id', $room_or_box_id)->update(['status_id' => 8]);
                        }

                        // get price room
                        $price = PriceRoom::select('price')
                            ->where('type_size_id', $order_detail->type_size_id)
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
                    // order box
                    elseif ($order_detail->type == 'box') {
                        // get box
                        $box = Box::where('status_id', 9)
                                ->where('space_id', $order->space_id)
                                ->where('type_size_id', $order_detail->type_size_id)
                                ->orderBy('id')
                                ->limit(1)->get();
                        if(isset($box)){
                            $room_or_box_id = $box[0]->id;
                            //change status box to fill
                            DB::table('boxes')->where('id', $room_or_box_id)->update(['status_id' => 8]);
                        }

                        // get price box
                        $price = PriceBox::select('price')
                            ->where('type_size_id', $order_detail->type_size_id)
                            ->get();
                        if(isset($price)){
                            $amount = $price[0]->price;
                        }else{
                            // change status room to empty when order failed to create
                            DB::table('boxes')->where('id', $room_or_box_id)->update(['status_id' => 9]);
                            return response()->json([
                                'status' =>false,
                                'message' => 'Not found price box.'
                            ]);
                        }                                                
                    }

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

}