<?php

namespace App\Http\Controllers\Api;

use App\Model\OrderDetail;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailResource;
use Illuminate\Http\Request;
use App\Http\Resources\AuthResource;
use App\Repositories\Contracts\OrderDetailRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderDetailController extends Controller
{
    protected $orderDetail;

    public function __construct(OrderDetailRepository $orderDetail)
    {
        $this->orderDetail = $orderDetail;
    }

    public function my_box(Request $request)
    {
        $user   = $request->user();
        $orders = $this->orderDetail->getMyBox($user->id); 

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
        $user   = $request->user();
        $orders = $this->orderDetail->getMyBoxHistory($user->id); 

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

    public function my_deliveries(Request $request)
    {
        $user   = $request->user();
        $orders = $this->orderDetail->getMyDeliveries($user->id);
        
        if(count($orders) > 0) {
            $data = OrderDetailResource::collection($orders);

            return $orders->toArray();
        }

        return response()->json([
            'status' => false,
            'message' => 'Data not found'
        ]);
    }

    public function getById($order_detail_id)
    {
        $orders = $this->orderDetail->getById($order_detail_id);

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
