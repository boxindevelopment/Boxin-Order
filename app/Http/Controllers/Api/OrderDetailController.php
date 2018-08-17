<?php

namespace App\Http\Controllers\Api;

use App\Model\OrderDetail;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailResource;
use Illuminate\Http\Request;
use App\Http\Resources\AuthResource;
use DB;

class OrderDetailController extends Controller
{

    public function my_box(Request $request)
    {
        $user = $request->user();

        $orders = OrderDetail::select('order_details.*', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('datediff("Day", order_details.end_date, order_details.start_date) as total_time'), DB::raw('datediff("Day", GETDATE(),order_details.start_date) as selisih'))
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('user_id', $user->id)
            ->where('order_details.status_id', '=', 4)
            ->get();


        if(count($orders) > 0) {
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

    public function my_box_history(Request $request)
    {
        $user = $request->user();

        $orders = OrderDetail::select('order_details.*', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('datediff("Day", order_details.end_date, order_details.start_date) as total_time'), DB::raw('datediff("Day", GETDATE(),order_details.start_date) as selisih'))
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('user_id', $user->id)
            ->where('order_details.status_id', '=', 12)
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

    public function my_deliveries(Request $request)
    {
        $user = $request->user();

        $orders = OrderDetail::select('order_details.*', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('DATEDIFF(day, order_details.end_date, order_details.start_date) as total_time'), DB::raw('DATEDIFF(day, GETDATE(), order_details.start_date) as selisih'))
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('user_id', $user->id)
            ->where('order_details.status_id', '!=', 4)
            ->where('order_details.status_id', '!=', 12)
            ->get();

        if(count($orders) > 0) {
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

}
