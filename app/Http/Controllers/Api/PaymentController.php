<?php

namespace App\Http\Controllers\Api;

use App\Model\Payment;
use App\Model\Box;
use App\Model\User;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\SpaceSmall;
use App\Model\PickupOrder;
use App\Model\UserDevice;
use App\Repositories\PaymentRepository;
use App\Repositories\ExtendPaymentRepository;
use App\Model\ExtendOrderDetail;
use App\Model\ExtendOrderPayment;
use App\Model\ChangeBoxPayment;
use App\Model\ChangeBox;
use App\Model\HistoryOrderDetailBox;
use App\Model\AddItem;
use App\Model\AddItemBox;
use App\Model\AddItemBoxPayment;
use App\Model\ReturnBoxPayment;
use App\Model\ReturnBoxes;
use App\Model\OrderTakePayment;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\ExtendOrderPaymentResource;
use App\Http\Resources\ExtendOrderDetailResource;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\Vtdirect;
use Carbon\ Carbon;
use Exception;
use Requests;
use Log;

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
  // TAKE
  // RETURN

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
            $check = Payment::where('order_id', $request->order_id)->get();
            if (count($check) > 0){
                $payment = $check->first();
              if($payment->status_id == 14){
                  return response()->json([
                      'status' => true,
                      // 'message' => 'Please wait while our admin is confirming the payment (1x24 hours).',
                      'message' => 'Payment created.',
                      'data' => new PaymentResource($payment)
                  ]);
              } else {
                  throw new Exception('Order has been paid.');
              }
              // return response()->json(['status' => false, 'message' => 'Order has been paid.'], 401);
            }

            //* data payment baru
            $invoice = 'PAY-ORDER-' . $request->order_id . $this->id_name();
            $itemID = 'ORDERID-'. $request->order_id;
            $info = 'Payment for order box id ' . $request->order_id;
            $url_redirect = $midtrans->purchase($user, $order->created_at, $invoice, $amount, $itemID, $info);
            if (empty($url_redirect)) {
              throw new Exception('Server is busy, please try again later');
            }

            $start_transaction = Carbon::parse($order->created_at);
            $expired_transaction = Carbon::parse($order->created_at)->addDays(1);

            $payment                               = new Payment;
            $payment->order_id                     = $request->order_id;
            $payment->user_id                      = $user->id;
            $payment->payment_type                 = 'midtrans';
            $payment->bank                         = null;
            $payment->amount                       = $amount;
            $payment->status_id                    = 14;
            $payment->midtrans_url                 = $url_redirect;
            $payment->midtrans_status              = 'pending';
            $payment->midtrans_start_transaction   = $start_transaction->toDateTimeString();
            $payment->midtrans_expired_transaction = $expired_transaction->toDateTimeString();
            $payment->id_name                      = $invoice;
            $payment->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 401);
        }

        return response()->json([
            'status' => true,
            // 'message' => 'Please wait while our admin is confirming the payment (1x24 hours).',
            'message' => 'Payment created.',
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
            // $client = new \GuzzleHttp\Client();
            // $response = $client->request('POST', $this->url . 'api/confirm-payment/' . $order->user_id, ['form_params' => [
            //   'status_id'       => $status,
            //   'order_detail_id' => $value->id
            // ]]);
            $response = Requests::post($this->url . 'api/confirm-payment/' . $order->user_id, [], $params, []);
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
            $checkPayment = ExtendOrderPayment::where('extend_id', (int)$request->extend_id)->where('status_id', 14)->first();
            //* jika data sudah ada
            if ($checkPayment) {
              // throw new Exception('Order has been paid.');
              // return response()->json(['status' => false, 'message' => 'Order has been paid.'], 401);
              return response()->json([
                'status'  => true,
                'message' => 'Payment already created.',
                'data'    => $checkPayment
              ]);
            }

            //* data payment baru
            $invoice = 'PAY-XTEND-' . $request->extend_id . $this->id_name_extend();
            $itemID = 'EXTENDID-'. $request->extend_id;
            $info = 'Payment for extend box id ' . $request->extend_id;
            $url_redirect = $midtrans->purchase($user, $ex_order->created_at, $invoice, $amount, $itemID, $info);
            if (empty($url_redirect)) {
              throw new Exception('Server is busy, please try again later');
            }

            $start_transaction = Carbon::parse($ex_order->created_at);
            $expired_transaction = Carbon::parse($ex_order->created_at)->addDays(1);

            $payment                               = new ExtendOrderPayment;
            $payment->extend_id                    = $request->extend_id;
            $payment->order_detail_id              = $ex_order->order_detail_id;
            $payment->user_id                      = $user->id;
            $payment->payment_type                 = 'midtrans';
            $payment->bank                         = null;
            $payment->amount                       = $amount;
            $payment->status_id                    = 14;
            $payment->id_name                      = $invoice;
            $payment->midtrans_url                 = $url_redirect;
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
            // 'message' => 'Please wait while our admin is confirming the payment (1x24 hours).',
            'message' => 'Payment created.',
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

          if ($status == 5) {
              $orderDetails           = OrderDetail::findOrFail($ex_order_details->order_detail_id);
              $orderDetails->amount   = $ex_order_details->total_amount; // total amount dari durasi baru dan lama
              $orderDetails->end_date = $ex_order_details->new_end_date; // durasi tanggal berakhir yang baru
              $orderDetails->duration = $ex_order_details->new_duration; // total durasi
              $orderDetails->save();
          }

          if ($status == 5 || $status == 6){
            $params['status_id'] =  $status;
            $params['order_detail_id'] = $ex_order_details->order_detail_id;
            $user_id = $ex_order_details->user_id;
            $userDevice = UserDevice::where('user_id', $user_id)->get();
            if(count($userDevice) > 0){
                $response = Requests::post($this->url . 'api/confirm-payment/' . $user_id, [], $params, []);
              // $client = new \GuzzleHttp\Client();
              // $response = $client->request('POST', $this->url . 'api/confirm-payment/' . $user_id, ['form_params' => [
              //   'status_id'       => $status,
              //   'order_detail_id' => $ex_order_details->order_detail_id
              // ]]);
            }
          }
      }
    }


    public function callbackNotif(Request $request)
    {
        // Log::info("Midtrans Notication");
      $midtrans = new Vtdirect();
      $json_result = file_get_contents('php://input');
      $result = json_decode($json_result);

      // Log::info("Midtrans Callback");

      $notif = null;
      if ($result) {
        $notif = $midtrans->checkStatus($result->order_id);
      }

      $transaction = isset($notif->transaction_status) ? $notif->transaction_status : $notif['transaction_status'];
      $type        = isset($notif->payment_type) ? $notif->payment_type : $notif['payment_type'];
      $order_id    = isset($notif->order_id) ? $notif->order_id : $notif['order_id'];
      $fraud       = isset($notif->fraud_status) ? $notif->fraud_status : $notif['fraud_status'];

      if ($transaction == 'pending') {
        // do nothing
        return response()->json([
          'status'  => true,
          'message' => 'pending',
          'response' => $notif
        ]);
      } else if ($transaction == 'settlement') {
        // sukses
        self::konekDB($order_id, 'success', $notif);
        return response()->json([
          'status'  => true,
          'message' => 'success',
          'response' => $notif
        ]);
      } else {
        self::konekDB($order_id, 'reject', $notif);
        return response()->json([
          'status'  => true,
          'message' => 'Rejected',
          'response' => $notif
        ], 422);
      }
    }


    private function konekDB($str, $status, $notif)
    {
      if (strpos($str, '-') !== false) {
        $db = explode('-',$str);
        $cek = '';
        if (count($db) > 0) {
          $cek = $db[1];
        }
         // Log::info("CEK" . $cek);
        switch ($cek) {
            // PAY-ORDER-
          case 'ORDER':
            // Log::info("ORDER");
            $varss = self::updatePaymentOrder($str, $status, $notif);
            break;

          case 'XTEND':
            // Log::info("XTEND");
            $varss = self::updatePaymentExtend($str, $status, $notif);
            break;

          case 'CHBOX':
            // Log::info("CHBOX");
            $varss = self::updatePaymentChangebox($str, $status, $notif);
            break;

          case 'ADDIT':
            $varss = self::updatePaymentAdditem($str, $status, $notif);
            break;

          case 'RTBOX':
            $varss = self::updatePaymentReturnbox($str, $status, $notif);
            break;

          case 'TAKE':
            $varss = self::updatePaymentTake($str, $status, $notif);
            break;

          case 'BACK':
            $varss = self::updatePaymentBackWarehouse($str, $status, $notif);
            break;

          default:
            # code...
            break;
        }
      }
    }

    // 5 = Success
    // 6 = Failed
    // 7 = Approved (*)
    // 8 = Rejected (*)
    protected function updatePaymentOrder($str, $stat, $notif)
    {
      $status = 8;
      if ($stat == 'Success') {
        $status = 7;
    } else if ($stat == 'success') {
        $status = 5;
    }

      DB::beginTransaction();
      try {
        // Log::info("Payment Order");
        $payment = Payment::where('id_name', $str)->where('status_id', 14)->first();
        if (empty($payment)) {
          throw new Exception("Edit status order payment failed.");
          // Log::info("Payment Order");
        }

        $order_id                   = $payment->order_id;
        $payment->status_id         = intval($status);
        $payment->midtrans_response = json_encode($notif);
        $payment->midtrans_status   = $notif['transaction_status'];
        $payment->save();

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

        if ($status == 8) {
          for ($i=0; $i < count($array); $i++) {
            self::backToEmpty($array[$i]['types_of_box_room_id'], $array[$i]['room_or_box_id']);
          }
        }

        foreach ($order_details as $key => $value) {
          if ($status == 7 || $status == 8 || $status == 5){
            $params['status_id']       = $status;
            $params['order_detail_id'] = $value->id;
            $userDevice = UserDevice::where('user_id', $order->user_id)->get();
            if(count($userDevice) > 0){
                // $response = Requests::post($this->url . 'api/confirm-payment/' . $order->user_id, [], $params, []);
              $client = new \GuzzleHttp\Client();
              $response = $client->request('POST', env('APP_NOTIF') . 'api/confirm-payment/' . $order->user_id, ['form_params' => [
                'status_id'       => $status,
                'order_detail_id' => $value->id
              ]]);
            }
          }
        }

        DB::commit();
        return true;
      } catch (Exception $th) {
        DB::rollback();
        return false;
      }
    }

    protected function updatePaymentExtend($str, $stat, $notif)
    {
      $status = 8;
      if ($stat == 'approved') {
        $status = 7;
      } else if ($stat == 'success') {
          $status = 5;
      }

      DB::beginTransaction();
      try {
        $payment = ExtendOrderPayment::where('id_name', $str)->where('status_id', 14)->first();
        if (empty($payment)) {
          throw new Exception("Edit status extend payment failed.");
        }

        $extend_id                  = $payment->extend_id;
        $payment->status_id         = $status;
        $payment->midtrans_response = json_encode($notif);
        $payment->midtrans_status   = $notif['transaction_status'];
        $payment->save();

        $ex_order_details = ExtendOrderDetail::find($extend_id);
        if ($ex_order_details) {
            $ex_order_details->status_id = $status;
            $ex_order_details->save();

            if ($status == 7) {
                $orderDetails           = OrderDetail::findOrFail($ex_order_details->order_detail_id);
                $orderDetails->amount   = $ex_order_details->total_amount;                              // total amount dari durasi baru dan lama
                $orderDetails->end_date = $ex_order_details->new_end_date;                              // durasi tanggal berakhir yang baru
                $orderDetails->duration = $ex_order_details->new_duration;                              // total durasi
                $orderDetails->save();
            }

            if ($status == 7 || $status == 8 || $status == 5){
              $params['status_id'] =  $status;
              $params['order_detail_id'] = $ex_order_details->order_detail_id;
              $userDevice = UserDevice::where('user_id', $ex_order_details->user_id)->get();
              if(count($userDevice) > 0){
                  // $response = Requests::post($this->url . 'api/confirm-payment/' . $user_id, [], $params, []);
                $client = new \GuzzleHttp\Client();
                $response = $client->request('POST', env('APP_NOTIF') . 'api/confirm-payment/' . $user_id, ['form_params' => [
                  'status_id'       => $status,
                  'order_detail_id' => $ex_order_details->order_detail_id
                ]]);
              }
            }
        }

        DB::commit();
        return true;
      } catch (\Exception $th) {
        DB::rollback();
        return false;
      }
    }

    protected function updatePaymentChangebox($str, $stat, $notif)
    {
      $status = 8;
      if ($stat == 'approved') {
        $status = 7;
      } else if ($stat == 'success') {
          $status = 5;
      }

      DB::beginTransaction();
      try {
        $payment = ChangeBoxPayment::where('id_name', $str)->where('status_id', 14)->first();
        if (empty($payment)) {
          throw new Exception("Edit status change box payment failed.");
        }

        $change_box_id      = $payment->change_box_id;
        $order_detail_id    = $payment->order_detail_id;
        $payment->status_id = $status;
        $payment->midtrans_response = json_encode($notif);
        $payment->midtrans_status   = $notif['transaction_status'];
        $payment->save();

        $cb = ChangeBox::find($change_box_id);
        if ($cb) {
          $cb->status_id = $status;
          $cb->save();
        }

        //change status on table change_boxes
        // $order_detail_box = OrderDetailBox::where('order_detail_id', $order_detail_id)->pluck('id')->toArray();
        // if (count($order_detail_box) > 0) {
        //     ChangeBox::whereIn('order_detail_box_id', $order_detail_box)->where('order_detail_id', $order_detail_id)->update(['status_id' => $status]);
        // }

        DB::commit();
        return true;
      } catch (Exception $th) {
        DB::rollback();
        return false;
      }

    }

    protected function updatePaymentAdditem($str, $stat, $notif)
    {
      $status = 8;
      if ($stat == 'approved') {
        $status = 7;
      } else if ($stat == 'success') {
          $status = 5;
      }

      DB::beginTransaction();
      try {
        $payment = AddItemBoxPayment::where('id_name', $str)->where('status_id', 14)->first();
        if (empty($payment)) {
          throw new Exception("Edit status change box payment failed.");
        }

        $add_item_box_id = $payment->add_item_box_id;
        $order_detail_id = $payment->order_detail_id;
        $payment->status_id = $status;
        $payment->midtrans_response = json_encode($notif);
        $payment->midtrans_status   = $notif['transaction_status'];
        $payment->save();

        //change status on table add_item
        $add_item = AddItemBox::find($add_item_box_id);
        if (!empty($add_item)) {
          $add_item->status_id = $status;
          $add_item->save();
        }

        DB::commit();
        return true;
      } catch (Exception $th) {
        DB::rollback();
        return false;
      }
    }

    protected function updatePaymentReturnbox($str, $stat, $notif)
    {
      $status = 8;
      if ($stat == 'approved') {
        $status = 7;
      } else if ($stat == 'success') {
          $status = 5;
      }

      DB::beginTransaction();
      try {
        $payment = ReturnBoxPayment::where('id_name', $str)->where('status_id', 14)->first();
        if (empty($payment)) {
          throw new Exception("Edit status return box payment failed.");
        }

        $order_detail_id            = $payment->order_detail_id;
        $payment->status_id         = $status;
        $payment->midtrans_response = json_encode($notif);
        $payment->midtrans_status   = $notif['transaction_status'];
        $payment->save();

        $orderdetail = OrderDetail::find($order_detail_id);
        if (!empty($orderdetail)) {
          $orderdetail->status_id = $status;
          $orderdetail->save();
        }

        $order = Order::find($orderdetail->order_id);
        if (!empty($order)) {
          $order->status_id = $status;
          $order->save();
        }

        $return_box = ReturnBoxes::where('order_detail_id', $order_detail_id)->first();
        if (!empty($return_box)) {
          $return_box->status_id = $status;
          $return_box->save();
        }

        DB::commit();
        return true;
      } catch (Exception $th) {
        DB::rollback();
        return false;
      }

    }

    protected function updatePaymentTake($str, $stat, $notif)
    {
        $status = 8;
        if ($stat == 'approved') {
            $status = 7;
        } else if ($stat == 'success') {
            $status = 5;
        }

        DB::beginTransaction();
        try {
            $orderTakePayment = OrderTakePayment::where('id_name', $str)->where('status_id', 14)->first();
            if (empty($orderTakePayment)) {
                throw new Exception("Edit status order take payment failed.");
            }

            $order_detail_id                        = $orderTakePayment->order_detail_id;
            $orderTakePayment->status_id            = $status;
            $orderTakePayment->midtrans_response    = json_encode($notif);
            $orderTakePayment->save();

            $orderdetail = OrderDetail::find($order_detail_id);
            if (!empty($orderdetail)) {
                $orderdetail->status_id = $status;
                $orderdetail->save();
            }

            $order = Order::find($orderdetail->order_id);
            if (!empty($order)) {
                $order->status_id = $status;
                $order->save();
            }

            $orderTake = OrderTake::where('order_detail_id', $order_detail_id)->first();
            if (!empty($orderTake)) {
                $orderTake->status_id = $status;
                $orderTake->save();
            }

            DB::commit();
            return true;
        } catch (Exception $th) {
            DB::rollback();
            return false;
        }

    }

    protected function updatePaymentBackWarehouse($str, $stat, $notif)
    {
        $status = 8;
        if ($stat == 'approved') {
            $status = 7;
        } else if ($stat == 'success') {
            $status = 5;
        }

        DB::beginTransaction();
        try {
            $orderBackWarehousePayment = OrderBackWarehousePayment::where('id_name', $str)->where('status_id', 14)->first();
            if (empty($orderBackWarehousePayment)) {
                throw new Exception("Edit status order back warehouse payment failed.");
            }

            $order_detail_id                                = $orderBackWarehousePayment->order_detail_id;
            $orderBackWarehousePayment->status_id           = $status;
            $orderBackWarehousePayment->midtrans_response   = json_encode($notif);
            $orderBackWarehousePayment->save();

            $orderdetail = OrderDetail::find($order_detail_id);
            if (!empty($orderdetail)) {
                $orderdetail->status_id = $status;
                $orderdetail->save();
            }

            $order = Order::find($orderdetail->order_id);
            if (!empty($order)) {
                $order->status_id = $status;
                $order->save();
            }

            $orderBackWarehouse = OrderBackWarehouse::where('order_detail_id', $order_detail_id)->first();
            if (!empty($orderBackWarehouse)) {
                $orderBackWarehouse->status_id = $status;
                $orderBackWarehouse->save();
            }

            DB::commit();
            return true;
        } catch (Exception $th) {
            DB::rollback();
            return false;
        }

    }

    public function showFinish()
    {
       return 'Payment Finish';
    }

    public function showUnfinish()
    {
       return 'Payment Unfinish';
    }

    public function showError()
    {
       return 'Payment Error';
    }


}
