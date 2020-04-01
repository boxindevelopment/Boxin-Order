<?php

namespace App\Http\Controllers\Api;

use App\Model\OrderDetail;
use App\Model\ExtendOrderDetail;
use App\Model\OrderTake;
use App\Model\TransactionLog;
use App\Model\OrderBackWarehouse;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\ExtendOrderDetailResource;
use App\Http\Resources\TransactionLogResource;
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

    public function my_space(Request $request)
    {
        $user   = $request->user();
        $params = array();
        $params['user_id'] = $user->id;
        $params['limit']   = intval($request->limit);
        $params['status_disable']   = 14;
        $params['search'] = ($request->search) ? $request->search : '';
        $orders = $this->orderDetail->findPaginateMySpace($params);
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

    public function my_box(Request $request)
    {
        $user   = $request->user();
        $params = array();
        $params['user_id'] = $user->id;
        $params['place'] = ($request->place) ? $request->place : '';
        $params['search'] = ($request->search) ? $request->search : '';
        $params['limit']   = intval($request->limit);
        $params['status_disable']   = 14;
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

    public function my_box_pace(Request $request)
    {
        $user   = $request->user();
        $params = array();
        $params['user_id'] = $user->id;
        $params['search'] = ($request->search) ? $request->search : '';
        $params['limit']   = intval($request->limit);
        $orderDetails = $this->orderDetail->findPaginateMyBoxSpace($params);
        $orderArrays = array();

        if($orderDetails) {
            $cekOrderId = 0;
            $no = 0;
            foreach ($orderDetails as $k => $v) {
                if (in_array($v->status_id, array(8, 10, 11, 14, 15, 24))) {
                    if($cekOrderId != $v->order_id){
                        $orderDetails[$k] = $v->toSearchableArray();
                        $no++;
                    } else {
                        unset($orderDetails[$k]);
                    }
                } else {
                    $orderDetails[$k] = $v->toSearchableArray();
                    $no++;
                }
                $cekOrderId = $v->order_id;
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Data not found.'], 301);
        }

        return response()->json($orderDetails);
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
            // foreach ($orders as $k => $v) {
            //     $orders[$k] = $v->toSearchableArray();
            // }
            $cekOrderId = 0;
            $no = 0;
            foreach ($orders as $k => $v) {
                if (in_array($v->status_id, array(4))) {
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

        $orders = $orders->first();

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

        // hitung jumlah amount sebelum dan sesudah extend
        $total_amount = $nominal * $new_duration;

        $extend_order                         = new ExtendOrderDetail;
        $extend_order->order_detail_id        = $order_detail_id;
        $extend_order->order_id               = $orders->order_id;
        $extend_order->user_id                = $user->id;
        $extend_order->extend_duration        = $request->duration;                             // durasi inputan
        $extend_order->remaining_duration     = $orders->duration;                              // durasi sebelumnya
        $extend_order->amount                 = $amount;
        $extend_order->end_date_before        = $orders->end_date;
        $extend_order->new_end_date           = $new_end_date;
        $extend_order->new_duration           = $new_duration;
        $extend_order->total_amount           = $total_amount;
        $extend_order->payment_expired        = Carbon::now()->addDays(1)->toDateTimeString();
        $extend_order->payment_status_expired = 0;
        $extend_order->status_id              = 14;
        $extend_order->save();

        // Transaction Log Create
        $transactionLog = new TransactionLog;
        $transactionLog->user_id                        = $user->id;
        $transactionLog->transaction_type               = 'extend';
        $transactionLog->order_id                       = $extend_order->id;
        if($total_amount > 0){
            $transactionLog->status                         = 'Pend Payment';
        } else {
            $transactionLog->status                         = 'Pending';
        }
        $transactionLog->location_warehouse             = 'warehouse';
        $transactionLog->location_pickup                = 'warehouse';
        $transactionLog->datetime_pickup                =  Carbon::now();
        $transactionLog->types_of_box_space_small_id    = $orders->types_of_box_room_id;
        $transactionLog->space_small_or_box_id          = $orders->room_or_box_id;
        $transactionLog->amount                         = $total_amount;
        $transactionLog->created_at                     =  Carbon::now();
        $transactionLog->save();

        
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', env('APP_NOTIF') . 'api/extend', ['form_params' => [
        'status_id'       => $extend_order->status_id,
        'order_detail_id' => $order_detail_id,
        'extend_order_id' => $extend_order->id
        ]]);

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
        'data' => new ExtendOrderDetailResource($extend_order)
      ]);
    }

    public function take($order_detail_id, Request $request)
    {
      $user = $request->user();

      $validator = Validator::make($request->all(), [
        'types_of_pickup_id' => 'required',
        'date'               => 'required',
        'time'               => 'required',
        'address'            => 'required',
        'deliver_fee'        => 'required',
        'time_pickup'        => 'required'
      ]);

      if ($validator->fails()) {
          return response()->json([
              'status' => false,
              'message' => $validator->errors()
          ]);
      }

      $orderDetails = $this->orderDetail->getById($order_detail_id);
      if (count($orderDetails) < 1) {
          return response()->json([
              'status' => false,
              'message' => 'Data not found'
          ]);
      }
      $orderDetails = $orderDetails->first();
      if($orderDetails->status_id != 5 && $orderDetails->status_id != 7 && $orderDetails->status_id != 9){
          return response()->json([
              'status' => false,
              'message' => 'status failed'
          ]);
      }

      DB::beginTransaction();
      try {

        $orderDetails->place = 'house';
        $orderDetails->save();

        $orderTake                         = new OrderTake;
        $orderTake->types_of_pickup_id     = $request->types_of_pickup_id;                             // durasi inputan
        $orderTake->order_detail_id        = $order_detail_id;
        $orderTake->user_id       = $user->id;                                     // durasi inputan
        $orderTake->date                   = $request->date;                             // durasi inputan
        $orderTake->time                   = $request->time;                             // durasi inputan
        $orderTake->address                = $request->address;                             // durasi inputan
        $orderTake->deliver_fee            = $request->deliver_fee;                              // durasi sebelumnya
        $orderTake->time_pickup            = $request->time_pickup;
        $orderTake->note                   = $request->note;
        if($request->deliver_fee > 0){
            $orderTake->status_id          = 14;
        } else {
            $orderTake->status_id          = 11;
        }
        $orderTake->save();

        // Transaction Log Create
        $transactionLog = new TransactionLog;
        $transactionLog->user_id                        = $user->id;
        $transactionLog->transaction_type               = 'take';
        $transactionLog->order_id                       = $orderTake->id;
        if($request->deliver_fee > 0){
            $transactionLog->status                         = 'Pend Payment';
        } else {
            $transactionLog->status                         = 'Pending';
        }
        $transactionLog->location_warehouse             = 'house';
        $transactionLog->location_pickup                = 'warehouse';
        $transactionLog->datetime_pickup                =  Carbon::now();
        $transactionLog->types_of_box_space_small_id    = $orderDetails->types_of_box_room_id;
        $transactionLog->space_small_or_box_id          = $orderDetails->room_or_box_id;
        $transactionLog->amount                         = $request->deliver_fee;
        $transactionLog->created_at                     =  Carbon::now();
        $transactionLog->save();

        
        if($request->types_of_pickup_id > 1){
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', env('APP_NOTIF') . 'api/take/' . $orderTake->id, ['form_params' => [
            'status_id'       => $orderTake->status_id,
            'order_detail_id' => $orderDetails->id
            ]]);
        }



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
        'data' => new TransactionLogResource($transactionLog)
      ]);
    }

    public function backWarehouse($order_detail_id, Request $request)
    {
      $user = $request->user();

      $validator = Validator::make($request->all(), [
        'types_of_pickup_id' => 'required',
        'date'               => 'required',
        'time'               => 'required',
        'address'            => 'required',
        'deliver_fee'        => 'required',
        'time_pickup'        => 'required'
      ]);

      if ($validator->fails()) {
          return response()->json([
              'status' => false,
              'message' => $validator->errors()
          ]);
      }

      $orderDetails = $this->orderDetail->getById($order_detail_id);
      if (count($orderDetails) < 1) {
          return response()->json([
              'status' => false,
              'message' => 'Data not found'
          ]);
      }
      if($orderDetails->status_id != 5 && $orderDetails->status_id != 7 && $orderDetails->status_id != 9){
          return response()->json([
              'status' => false,
              'message' => 'status failed'
          ]);
      }

      DB::beginTransaction();
      try {

        $orderDetails = $orderDetails->first();
        $orderDetails->place = 'warehouse';
        $orderDetails->save();

        $orderBackWarehouse                         = new OrderBackWarehouse;
        $orderBackWarehouse->types_of_pickup_id     = $request->types_of_pickup_id;                             // durasi inputan
        $orderBackWarehouse->order_detail_id        = $order_detail_id->id;
        $orderBackWarehouse->user_id                = $user->id;                            // durasi inputan
        $orderBackWarehouse->date                   = $request->date;                             // durasi inputan
        $orderBackWarehouse->time                   = $request->time;                             // durasi inputan
        $orderBackWarehouse->address                = $request->address;                             // durasi inputan
        $orderBackWarehouse->deliver_fee            = $request->deliver_fee;                              // durasi sebelumnya
        $orderBackWarehouse->time_pickup            = $request->time_pickup;
        $orderBackWarehouse->note                   = $request->note;
        if($request->deliver_fee > 0){
            $orderBackWarehouse->status_id          = 14;
        } else {
            $orderBackWarehouse->status_id          = 11;
        }
        $orderBackWarehouse->save();

        // Transaction Log Create
        $transactionLog = new TransactionLog;
        $transactionLog->user_id                        = $user->id;
        $transactionLog->transaction_type               = 'back warehouse';
        $transactionLog->order_id                       = $orderBackWarehouse->id;
        if($request->deliver_fee > 0){
            $transactionLog->status                         = 'Pend Payment';
        } else {
            $transactionLog->status                         = 'Pending';
        }
        $transactionLog->location_warehouse             = 'house';
        $transactionLog->location_pickup                = 'warehouse';
        $transactionLog->datetime_pickup                =  Carbon::now();
        $transactionLog->types_of_box_space_small_id    = $orderDetails->types_of_box_room_id;
        $transactionLog->space_small_or_box_id          = $orderDetails->room_or_box_id;
        $transactionLog->amount                         = $request->deliver_fee;
        $transactionLog->created_at                     =  Carbon::now();
        $transactionLog->save();

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
        'data' => new TransactionLogResource($transactionLog)
      ]);
    }

}
