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
        $params = array();
        $params['user_id'] = $user->id;
        $params['limit']   = intval($request->limit);
        $orders = $this->orderDetail->findPaginateMyBox($params);

        if($orders) {
            foreach ($orders as $k => $v) {
                $orders[$k] = $v->toSearchableArray();
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Data not found.'], 301);
        }

        return response()->json($orders);
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
