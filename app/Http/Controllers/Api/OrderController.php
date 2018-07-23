<?php

namespace App\Http\Controllers\Api;

use App\Entities\Order;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::all();
        if(count($orders) != 0) {
            $data = OrderResource::collection($orders);

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
            'city' => 'required',
            'area' => 'required',
            'space' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        try {
            $order = new Order;
            $order->area_id = $request->area;
            $order->space_id = $request->space;
            $order->user_id = $request->user;
            $order->date = Carbon::now()->toDateTimeString();
            $order->save();
        } catch (\Exception $e) {
            return response()->json([
                'status' =>false,
                'message' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Order berhasil ditambahkan',
            'data' => new OrderResource($order)
        ]);
    }

    public function pickBox(Request $request, Order $order)
    {
        $validator = \Validator::make($request->all(), [
            'box' => 'required',
            'qty' => 'required|numeric'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        try {
            $order->box = $request->box;
            $order->box_qty = $request->qty;
            $order->save();
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Box berhasil dipilih',
            'data' => new OrderResource($order->fresh())
        ]);
    }

    public function setDuration(Request $request, Order $order)
    {
        $validator = \Validator::make($request->all(), [
            'duration' => 'required|numeric'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        try {
            $total = $order->boxes->price * $request->duration;

            $order->duration = $request->duration;
            $order->total = $total;
            $order->save();
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Durasi order berhasil ditambahkan',
            'data' => new OrderResource($order->fresh())
        ]);
    }
}