<?php

namespace App\Http\Controllers\Api;

use App\Model\OrderDetail;
use App\Model\ExtendOrderDetail;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\ExtendOrderDetailResource;
use Illuminate\Http\Request;
use App\Http\Resources\AuthResource;
use App\Repositories\Contracts\OrderDetailRepository;
use App\Repositories\Contracts\PriceRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Validator;
use Carbon\Carbon;
use DB;

class OrderDetailController extends Controller
{
    protected $orderDetail;
    protected $price;

    public function __construct(OrderDetailRepository $orderDetail, PriceRepository $price)
    {
        $this->orderDetail = $orderDetail;
        $this->price = $price;
    }

    public function my_box(Request $request)
    {
        $user   = $request->user();
        $params = array();
        $params['user_id'] = $user->id;
        $params['limit']   = intval($request->limit);
        $orders = $this->orderDetail->findPaginateMyBox($params);
        $orderArrays = array();

        if($orders) {
            $cekOrderId = 0;
            $no = 0;
            foreach ($orders as $k => $v) {
                if (in_array($v->status_id, array(8, 10, 11, 14, 15, 24))) {
                    if($cekOrderId != $v->order_id){
                        $orders[$k] = $v->toSearchableArray();
                        $no++;
                    } else {
                        unset($orders[$k]);
                    }
                } else {
                    $orders[$k] = $v->toSearchableArray();
                    $no++;
                }
                $cekOrderId = $v->order_id;
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Data not found.'], 301);
        }

        return response()->json($orders);
    }

    public function my_item(Request $request)
    {
        $user   = $request->user();
        $params = array();
        $params['user_id'] = $user->id;
        $params['limit']   = intval($request->limit);
        $params['search']  = $request->input('search');
        $orders = $this->orderDetail->findPaginateMyItem($params);

        if($orders) {
            foreach ($orders as $k => $v) {
                $orders[$k] = $v->toSearchableArray();
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Data not found.'], 301);
        }

        return response()->json($orders);
    }

    public function my_history(Request $request)
    {
        $user   = $request->user();
        $params = array();
        $params['user_id'] = $user->id;
        $params['limit']   = intval($request->limit);
        $orders = $this->orderDetail->findPaginateMyBoxHistory($params);

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

    public function extendOrderDetail($order_detail_id, Request $request)
    {
      $user = $request->user();

      $validator = Validator::make($request->all(), [
        'types_of_duration_id' => 'required',
        'duration'             => 'required'
      ]);

      if ($validator->fails()) {
          return response()->json([
              'status' => false,
              'message' => $validator->errors()
          ]);
      }

      $orders = $this->orderDetail->getById($order_detail_id);
      if (count($orders) < 1) {
          return response()->json([
              'status' => false,
              'message' => 'Data not found'
          ]);
      }

      DB::beginTransaction();
      try {

        $new_duration = (int)$request->duration + $orders->duration;
        $new_end_date = null;
        $amount       = 0;

        // weekly
        if ($request->types_of_duration_id == 2 || $request->types_of_duration_id == '2') {
            $end_date     = (int)$new_duration * 7;
            $new_end_date = date('Y-m-d', strtotime('+'.$end_date.' days', strtotime($orders->start_date)));
        }
        // monthly
        else if ($request->types_of_duration_id == 3 || $request->types_of_duration_id == '3') {
            $new_end_date = date('Y-m-d', strtotime('+'.$new_duration.' month', strtotime($orders->start_date)));
        }
        // 6 month
        else if ($request->types_of_duration_id == 7 || $request->types_of_duration_id == '7') {
            $end_date     = (int)$new_duration * 6;
            $new_end_date = date('Y-m-d', strtotime('+'.$end_date.' month', strtotime($orders->start_date)));
        }
        // annual (1 year)
        else if ($request->types_of_duration_id == 8 || $request->types_of_duration_id == '8') {
            $end_date     = (int)$new_duration * 12;
            $new_end_date = date('Y-m-d', strtotime('+'.$end_date.' month', strtotime($orders->start_date)));
        }

        // get price
        $nominal = 0;
        $price = $this->price->getPrice($orders->types_of_box_room_id, $orders->types_of_size_id, $request->types_of_duration_id, $orders->order->area_id);
        if ($price) {
            $nominal = $price->price;
            $amount  = $nominal * (int)$request->duration;
        } else {
          $type = '';
          if ($order_detail->types_of_box_room_id == 1 || $order_detail->types_of_box_room_id == "1") {
            $type = 'box';
          } else if ($order_detail->types_of_box_room_id == 2 || $order_detail->types_of_box_room_id == "2") {
            $type = 'space';
          }
          return response()->json(['status' => false, 'message' => 'Not found price ' . $type . '.']);
        }
        
        $extend_order                         = new ExtendOrderDetail;
        $extend_order->order_detail_id        = $order_detail_id;
        $extend_order->order_id               = $orders->order_id;
        $extend_order->user_id                = $user->id;
        $extend_order->extend_duration        = $request->duration;  // durasi inputan
        $extend_order->remaining_duration     = $orders->duration;   // durasi sebelumnya
        $extend_order->amount                 = $amount;
        $extend_order->end_date_before        = $orders->end_date;
        $extend_order->payment_expired        = Carbon::now()->addDays(1)->toDateTimeString();
        $extend_order->payment_status_expired = 0;
        $extend_order->status_id              = 14;
        $extend_order->save();

        // hitung jumlah amount sebelum dan sesudah extend
        $total_amount = $nominal * $new_duration;

        $orderDetails           = OrderDetail::findOrFail($order_detail_id);
        $orderDetails->amount   = $total_amount; // total amount dari durasi baru dan lama
        $orderDetails->end_date = $new_end_date; // durasi tanggal berakhir yang baru
        $orderDetails->duration = $new_duration; // total durasi
        $orderDetails->save();

        DB::commit();
      } catch (\Exception $x) {
        DB::rollback();
        return response()->json([
          'status' =>false,
          'message' => $x->getMessage()
        ], 422);
      }

      return response()->json([
        'status' => true,
        'message' => 'Your order has been made. Please complete the payment.',
        'data' => new ExtendOrderDetailResource($order)
      ]);
    }

}
