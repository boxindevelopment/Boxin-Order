<?php

namespace App\Http\Controllers\Api;

use App\Model\Payment;
use App\Model\Box;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\SpaceSmall;
use App\Model\PickupOrder;
use App\Model\UserDevice;
use App\Repositories\PaymentRepository;
use App\Repositories\ExtendPaymentRepository;
use App\Model\ExtendOrderDetail;
use App\Model\ExtendOrderPayment;
use App\Model\HistoryOrderDetailBox;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\ExtendOrderPaymentResource;
use App\Http\Resources\ExtendOrderDetailResource;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\Vtdirect;
use Carbon\ Carbon;
// use Requests;

class PaymentController extends Controller
{
  private $url;
	CONST DEV_URL = 'https://boxin-dev-notification.azurewebsites.net/';
	CONST LOC_URL = 'http://localhost:5252/';
  CONST PROD_URL = 'https://boxin-prod-notification.azurewebsites.net/';
  
  public function __construct()
  {
    $this->url = (env('DB_DATABASE') == 'coredatabase') ? self::DEV_URL : self::PROD_URL;
  }

  // === invoice ===
  // ADDIT
  // CHBOX
  // RTBOX
  // XTEND
  // ORDER

    // public function startPayment_backup(Request $request)
    // {
    //     $user = $request->user();

    //     $validator = \Validator::make($request->all(), [
    //         'order_id'          => 'required',
    //         'amount'            => 'required',
    //         'bank'              => 'required',
    //         'image'             => 'required',
    //     ]);

    //     if($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $validator->errors()
    //         ]);
    //     }

    //     try {
    //         $order = Order::find($request->order_id);
    //         if($order){
    //             $check = Payment::where('order_id', $request->order_id)->get();
    //             if(count($check)>0){
    //                 return response()->json(['status' => false, 'message' => 'Order has been paid.'], 401);
    //             }
    //             $data                    = $request->all();
    //             $payment                 = new Payment;
    //             $payment->order_id       = $request->order_id;
    //             $payment->user_id        = $user->id;
    //             $payment->payment_type   = 'transfer';
    //             $payment->bank           = $request->bank;
    //             $payment->amount         = $request->amount;
    //             $payment->status_id      = 15;
    //             if ($request->hasFile('image')) {
    //                 if ($request->file('image')->isValid()) {
    //                     $getimageName = time().'.'.$request->image->getClientOriginalExtension();
    //                     $image = $request->image->move(public_path('images/payment/order'), $getimageName);
    //                 }
    //             }
    //             $payment->image_transfer = $getimageName;
    //             $payment->id_name        = 'PAY'.$this->id_name();
    //             $payment->save();

    //             if($payment) {
    //                 //change status order
    //                 $order->status_id       = 15;
    //                 $order->save();
    //                 //change status order detail
    //                 $status_order = DB::table('order_details')->where('order_id', $request->order_id)->update(['status_id' => 15]);
    //             }
                
    //         } else {
    //             return response()->json(['status' => false, 'message' => 'Order Id not found'], 401);
    //         }
            
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ]);
    //     }

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Please wait while our admin is confirming the payment (1x24 hours).',
    //         'data' => new PaymentResource($payment->fresh())
    //     ]);
    // }

    public function startPayment(Request $request)
    {
        $midtrans = new Vtdirect();
        $user = $request->user();
        $validator = \Validator::make($request->all(), [
            'order_id' => 'required',
            'amount'   => 'required'
        ]);

        if($validator->fails()) {
          return response()->json([
            'status'  => false,
            'message' => $validator->errors()
          ]);
        }

        DB::beginTransaction();
        try {
            $order = Order::find($request->order_id);
            if (!$order) {
              throw new Exception('Order Id not found');
            }

            $amount = (int) $request->amount;
            $checkPayment = Payment::where('order_id', (int)$request->order_id)->where('amount', $amount)->first();
            //* jika data sudah ada
            if ($checkPayment) {
              $midtrans_data = $midtrans->checkstatus($checkPayment->id_name);
              if ($checkPayment->status_id != 5 || $checkPayment->status_id != 6) {
              $sukses_response = array('200', '201', '202');
                if (in_array($midtrans_data['status_code'], $sukses_response)) {
                  $newStatus = $midtrans_data['transaction_status'];
                  $checkPayment->midtrans_status = $newStatus;
                  $checkPayment->save();
                  // if ($newStatus == 'pending') {
                      // // change status payment
                      // $checkPayment->status_id = 15;
                      // $checkPayment->save();
                      // //change status order
                      // $order->status_id = 15;
                      // $order->save();
                      // //change status order detail
                      // $status_order = DB::table('order_details')->where('order_id', (int)$request->order_id)->update(['status_id' => 15]);
                  // }
                  if ($newStatus == 'pending') {

                  } else if ($newStatus == 'settlement' || $newStatus == 'success') {
                      // status code 5 = success
                      $checkPayment->status_id = 5;
                      $checkPayment->save();
                      self::paymentStatusOrder($request->order_id, 5);
                  } else {
                      $checkPayment->status_id = 6;
                      $checkPayment->save();
                      // status code 6 = failed
                      self::paymentStatusOrder($request->order_id, 6);
                  }
                }
              }

              DB::commit();
              return response()->json([
                'status'         => true,
                'message'        => 'Success get data',
                'data'           => new PaymentResource($checkPayment),
                'midtrans_check' => $midtrans_data
              ]);
            }

            //* data payment baru
            $invoice = 'PAY-ORDER' . $request->order_id . '-' . $this->id_name();
            $itemID = 'ORDERID'. $request->order_id;
            $info = 'Payment for order box id ' . $request->order_id;
            $midtrans_data = $midtrans->purchase($user, $order->created_at, $invoice, $amount, $itemID, $info);
            if (count($midtrans_data) == 0) {
              throw new Exception('Server is busy, please try again later');
            }

            $start_transaction = Carbon::parse($midtrans_data['start_time']);
            $expired_transaction = Carbon::parse($midtrans_data['start_time'])->addDays(1);

            $payment                               = new Payment;
            $payment->order_id                     = $request->order_id;
            $payment->user_id                      = $user->id;
            $payment->payment_type                 = 'midtrans';
            $payment->bank                         = null;
            $payment->amount                       = $amount;
            $payment->status_id                    = 14;
            $payment->midtrans_url                 = $midtrans_data['redirect_url'];
            $payment->midtrans_status              = 'pending';
            $payment->midtrans_start_transaction   = $start_transaction->toDateTimeString();
            $payment->midtrans_expired_transaction = $expired_transaction->toDateTimeString();
            $payment->id_name                      = $invoice;
            $payment->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 401);
        }

        return response()->json([
            'status' => true,
            // 'message' => 'Please wait while our admin is confirming the payment (1x24 hours).',
            'message' => 'Success submit to midtrans',
            'data' => new PaymentResource($payment)
        ]);
    }

    private function id_name()
    {
        $sql    = Payment::orderBy('number', 'desc')->whereRaw("MONTH(created_at) = " . date('m'))->first(['id_name', DB::raw('substring(id_name, len(id_name)-2,len(id_name)) as number')]);
        $number = isset($sql->number) ? $sql->number : 0;
        $code   = date('ymd') . str_pad($number + 1, 3, "0", STR_PAD_LEFT);
        return $code;
    }
    
    private function id_name_extend()
    {
        $sql    = ExtendOrderPayment::orderBy('number', 'desc')->whereRaw("MONTH(created_at) = " . date('m'))->first(['id_name', DB::raw('substring(id_name, len(id_name)-2,len(id_name)) as number')]);
        $number = isset($sql->number) ? $sql->number : 0;
        $code   = date('ymd') . str_pad($number + 1, 3, "0", STR_PAD_LEFT);
        return $code;
    }

    protected function paymentStatusOrder($order_id, $status) {
      /**
       * status:
       * 
       * 8 = reject
       * 7 = Approved
       * 5 = success
       * 6 = failed
       * 11 = pending
       * 14 = pend payment
       * 15 = confirming
       * 
       */
      $order            = Order::find($order_id);
      $order->status_id = $status;
      $order->save();

      $po            = PickupOrder::where('order_id', $order_id)->first();
      $po->status_id = $status;
      $po->save();

      $array = array();
      $order_details = OrderDetail::where('order_id', $order_id)->get();
      foreach ($order_details as $key => $value) {
        $array[] = array(
          'room_or_box_id'       => $value->room_or_box_id,
          'types_of_box_room_id' => $value->types_of_box_room_id
        );
        $value->status_id = $status;
        $value->save();
      }

      if ($status == 6) {
        for ($i=0; $i < count($array); $i++) { 
          self::backToEmpty($array[$i]['types_of_box_room_id'], $array[$i]['room_or_box_id']);
        }
      }

      foreach ($order_details as $key => $value) {
        if ($status == 5 || $status == 6){
          $params['status_id']       = $status;
          $params['order_detail_id'] = $value->id;
          $userDevice = UserDevice::where('user_id', $order->user_id)->get();
          if(count($userDevice) > 0){
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $this->url . 'api/confirm-payment/' . $order->user_id, ['form_params' => [
              'status_id'       => $status,
              'order_detail_id' => $value->id
            ]]);
            // $response = Request::post($this->url . 'api/confirm-payment/' . $order->user_id, [], $params, []);
          }
        }
      }
    }

    protected function backToEmpty($types_of_box_room_id, $id)
    {
      if ($types_of_box_room_id == 1 || $types_of_box_room_id == "1") {
        // order box
        $box = Box::find($id);
        if ($box) {
          $box->status_id = 10;
          $box->save();
        }
        // Box::where('id', $id)->update(['status_id' => 10]);
      }
      else if ($types_of_box_room_id == 2 || $types_of_box_room_id == "2") {
        // order room
        // change status room to empty
        $box = SpaceSmall::find($id);
        if ($box) {
          $box->status_id = 10;
          $box->save();
        }
        // SpaceSmall::where('id', $id)->update(['status_id' => 10]);
      }
    }

    // public function startPaymentOrderDetail(Request $request)
    // {
    //     $user = $request->user();
    //     $validator = \Validator::make($request->all(), [
    //         'extend_id' => 'required',
    //         'amount'    => 'required',
    //         'bank'      => 'required',
    //         'image'     => 'required',
    //     ]);

    //     if($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $validator->errors()
    //         ]);
    //     }

    //     try {
    //         $ex_order = ExtendOrderDetail::find($request->extend_id);
    //         if ($ex_order){
    //             $check = ExtendOrderPayment::where('extend_id', $request->extend_id)->get();
    //             if (count($check)>0){
    //                 return response()->json(['status' => false, 'message' => 'Order has been paid.'], 401);
    //             }

    //             $data                     = $request->all();
    //             $payment                  = new ExtendOrderPayment;
    //             $payment->extend_id       = $request->extend_id;
    //             $payment->order_detail_id = $ex_order->order_detail_id;
    //             $payment->user_id         = $user->id;
    //             $payment->payment_type    = 'transfer';
    //             $payment->bank            = $request->bank;
    //             $payment->amount          = $request->amount;
    //             $payment->status_id       = 15;
    //             if ($request->hasFile('image')) {
    //                 if ($request->file('image')->isValid()) {
    //                     $getimageName = time().'.'.$request->image->getClientOriginalExtension();
    //                     $image = $request->image->move(public_path('images/payment/order/detail'), $getimageName);
    //                 }
    //             }
    //             $payment->image_transfer = $getimageName;
    //             $payment->id_name        = 'PAY'.$this->id_name();
    //             $payment->save();

    //             if ($payment) {
    //                 $ex_order->status_id = 15;
    //                 $ex_order->save();
    //                 // $status_order = DB::table('order_details')->where('order_id', $request->order_id)->update(['status_id' => 15]);
    //             }
                
    //         }else {
    //             return response()->json(['status' => false, 'message' => 'Order Detail Id not found'], 401);
    //         }
            
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ]);
    //     }

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Please wait while our admin is confirming the payment (1x24 hours).',
    //         'data' => new ExtendOrderPaymentResource($payment->fresh())
    //     ]);
    // }
    
    public function startPaymentOrderDetail(Request $request)
    {
        $midtrans = new Vtdirect();
        $user = $request->user();
        $validator = \Validator::make($request->all(), [
            'extend_id' => 'required',
            'amount'    => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        DB::beginTransaction();
        try {
            $ex_order = ExtendOrderDetail::find($request->extend_id);
            if (!$ex_order) {
              throw new Exception('Order Detail Id not found');
            }

            $amount = (int) $request->amount;
            $checkPayment = ExtendOrderPayment::where('extend_id', (int)$request->extend_id)->where('amount', $amount)->first();
            //* jika data sudah ada
            if ($checkPayment) {
              $midtrans_data = $midtrans->checkstatus($checkPayment->id_name);
              if ($checkPayment->status_id != 5 || $checkPayment->status_id != 6) {
              $sukses_response = array('200', '201', '202');
                if (in_array($midtrans_data['status_code'], $sukses_response)) {
                  $newStatus = $midtrans_data['transaction_status'];
                  $checkPayment->midtrans_status = $newStatus;
                  $checkPayment->save();
                  // if ($newStatus == 'pending') {
                      // // change status payment
                      // $checkPayment->status_id = 15;
                      // $checkPayment->save();
                      // //change status order
                      // $ex_order->status_id = 15;
                      // $ex_order->save();
                  // } 
                  if ($newStatus == 'pending') {

                  } else if ($newStatus == 'settlement' || $newStatus == 'success') {
                      // status code 5 = success
                      $checkPayment->status_id = 5;
                      $checkPayment->save();
                      self::paymentStatusExtend((int)$request->extend_id, 5);
                  } else {
                      $checkPayment->status_id = 6;
                      $checkPayment->save();
                      // status code 6 = failed
                      self::paymentStatusExtend((int)$request->extend_id, 6);
                  }
                }
              }

              DB::commit();
              return response()->json([
                'status'         => true,
                'message'        => 'Success get data',
                'data'           => new ExtendOrderPaymentResource($checkPayment),
                'midtrans_check' => $midtrans_data
              ]);
            }

            //* data payment baru
            $invoice = 'PAY-XTEND' . $request->extend_id . '-' . $this->id_name_extend();
            $itemID = 'EXTENDID'. $request->extend_id;
            $info = 'Payment for extend box id ' . $request->extend_id;
            $midtrans_data = $midtrans->purchase($user, $ex_order->created_at, $invoice, $amount, $itemID, $info);
            if (count($midtrans_data) == 0) {
              throw new Exception('Server is busy, please try again later');
            }

            $start_transaction = Carbon::parse($midtrans_data['start_time']);
            $expired_transaction = Carbon::parse($midtrans_data['start_time'])->addDays(1);

            $payment                               = new Payment;
            $payment->extend_id                    = $request->extend_id;
            $payment->order_detail_id              = $ex_order->order_detail_id;
            $payment->user_id                      = $user->id;
            $payment->payment_type                 = 'midtrans';
            $payment->bank                         = null;
            $payment->amount                       = $amount;
            $payment->status_id                    = 14;
            $payment->id_name                      = $invoice;
            $payment->midtrans_url                 = $midtrans_data['redirect_url'];
            $payment->midtrans_status              = 'pending';
            $payment->midtrans_start_transaction   = $start_transaction->toDateTimeString();
            $payment->midtrans_expired_transaction = $expired_transaction->toDateTimeString();
            $payment->save();
                
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 401);
        }

        return response()->json([
            'status' => true,
            'message' => 'Please wait while our admin is confirming the payment (1x24 hours).',
            'data' => new ExtendOrderPaymentResource($payment)
        ]);
    }

    protected function paymentStatusExtend($extend_id, $status)
    {
      /**
       * status:
       * 
       * 8 = reject
       * 7 = Approved
       * 5 = success
       * 6 = failed
       * 11 = pending
       * 14 = pend payment
       * 15 = confirming
       * 
       */
      $ex_order_details = ExtendOrderDetail::find($extend_id);
      if ($ex_order_details) {
          $ex_order_details->status_id = intval($status);
          $ex_order_details->save();

          if ($request->status_id == 5) {
              $orderDetails           = OrderDetail::findOrFail($ex_order_details->order_detail_id);
              $orderDetails->amount   = $ex_order_details->total_amount;                              // total amount dari durasi baru dan lama
              $orderDetails->end_date = $ex_order_details->new_end_date;                              // durasi tanggal berakhir yang baru
              $orderDetails->duration = $ex_order_details->new_duration;                              // total durasi
              $orderDetails->save();
          }

          if ($request->status_id == 5 || $request->status_id == 6){
            $params['status_id'] =  $status;
            $params['order_detail_id'] = $ex_order_details->order_detail_id;
            $user_id = $ex_order_details->user_id;
            $userDevice = UserDevice::where('user_id', $user_id)->get();
            if(count($userDevice) > 0){
                // $response = Requests::post($this->url . 'api/confirm-payment/' . $user_id, [], $params, []);
              $client = new \GuzzleHttp\Client();
              $response = $client->request('POST', $this->url . 'api/confirm-payment/' . $order->user_id, ['form_params' => [
                'status_id'       => $status,
                'order_detail_id' => $ex_order_details->order_detail_id
              ]]);
            }
          }
      }
    }


}