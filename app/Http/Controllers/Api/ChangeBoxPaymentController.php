<?php

namespace App\Http\Controllers\Api;

use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\OrderDetailBox;
use App\Model\ChangeBox;
use App\Model\ChangeBoxPayment;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChangeBoxPaymentResource;
use Illuminate\Http\Request;
use DB;

class ChangeBoxPaymentController extends Controller
{

  private $url;
	CONST DEV_URL = 'https://boxin-dev-notification.azurewebsites.net/';
	CONST LOC_URL = 'http://localhost:5252/';
  CONST PROD_URL = 'https://boxin-prod-notification.azurewebsites.net/';
  
  public function __construct()
  {
    $this->url = (env('DB_DATABASE') == 'coredatabase') ? self::DEV_URL : self::PROD_URL;
  }
  
    // public function startPayment(Request $request)
    // {
    //     $user = $request->user();

    //     $validator = \Validator::make($request->all(), [
    //         'order_detail_id'   => 'required',
    //         'change_box_id'   => 'required',
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
    //         $change_box_id = ChangeBox::find($request->change_box_id);
    //         if ($change_box_id){
    //             $check = ChangeBoxPayment::where('change_box_id', $request->change_box_id)->where('status_id', '7')->get();
    //             if (count($check) > 0) {
    //                 return response()->json(['status' => false, 'message' => 'Request has been paid.'], 401);
    //             } else {
    //                 $data                     = $request->all();
    //                 $payment                  = new ChangeBoxPayment;
    //                 $payment->order_detail_id = $request->order_detail_id;
    //                 $payment->user_id         = $user->id;
    //                 $payment->payment_type    = 'transfer';
    //                 $payment->bank            = $request->bank;
    //                 $payment->amount          = $request->amount;
    //                 $payment->status_id       = 15;
    //                 $payment->change_box_id   = $request->change_box_id;
                    
    //                 if ($request->hasFile('image')) {
    //                     if ($request->file('image')->isValid()) {
    //                         $getimageName = time().'.'.$request->image->getClientOriginalExtension();
    //                         $image = $request->image->move(public_path('images/payment/changebox'), $getimageName);
                
    //                     }
    //                 }
    //                 $payment->image_transfer = $getimageName;
    //                 $payment->id_name        = 'PAYCB'.$this->id_name();
    //                 $payment->save();

    //                 // if($payment){
    //                 //     //change status order detail
    //                 //     $order_detail->status_id       = 15;
    //                 //     $order_detail->save();                        
    //                 // }
    //             }
                
    //         }else {
    //             return response()->json(['status' => false, 'message' => 'Order detail Id not found'], 401);
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
    //         'data' => new ChangeBoxPaymentResource($payment)
    //     ]);
    // }
    
    public function startPayment(Request $request)
    {
        $user = $request->user();
        $validator = \Validator::make($request->all(), [
            'order_detail_id' => 'required',
            'change_box_id'   => 'required',
            'amount'          => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        DB::beginTransaction();
        try {
            $order_detail = OrderDetail::find($request->order_detail_id);
            if (!$order_detail) {
              throw new Exception("Order detail Id not found");
            }

            $change_box = ChangeBox::find($request->change_box_id);
            if (!$change_box) {
              throw new Exception("Change box id not found");
            }

            $amount = (int) $request->amount;
            $checkPayment = ChangeBoxPayment::where('change_box_id', $request->change_box_id)->where('user_id', $user->id)->first();
            //* jika data sudah ada
            if ($checkPayment) {
              $midtrans_data = $midtrans->checkstatus($checkPayment->id_name);
              if ($checkPayment->status_id != 5 || $checkPayment->status_id != 6) {
              $sukses_response = array('200', '201', '202');
                if (in_array($midtrans_data['status_code'], $sukses_response)) {
                  $newStatus = $midtrans_data['transaction_status'];
                  $checkPayment->midtrans_status = $newStatus;
                  $checkPayment->save();
                  if ($newStatus == 'settlement' || $newStatus == 'success') {
                      // status code 5 = success
                      $checkPayment->status_id = 5;
                      $checkPayment->save();

                      // TODO
                      $change_box->status_id = 5;
                      $change_box->save();
                  } else {
                      // status code 6 = failed
                      $checkPayment->status_id = 6;
                      $checkPayment->save();

                      // TODO
                      $change_box->status_id = 6;
                      $change_box->save();
                  }
                }
              }

              DB::commit();
              return response()->json([
                'status'         => true,
                'message'        => 'Success get data',
                'data'           => new ChangeBoxPaymentResource($checkPayment),
                'midtrans_check' => $midtrans_data
              ]);
            }

            //* data payment baru
            $invoice = 'PAY-CHBOX' . $request->change_box_id . '-' . $this->id_name();
            $itemID = 'CHANGEBOXID'. $request->change_box_id;
            $info = 'Payment for changing box id ' . $request->change_box_id;
            $midtrans_data = $midtrans->purchase($user, $change_box->created_at, $invoice, $amount, $itemID, $info);
            if (count($midtrans_data) == 0) {
              throw new Exception('Server is busy, please try again later');
            }

            $start_transaction = Carbon::parse($midtrans_data['start_time']);
            $expired_transaction = Carbon::parse($midtrans_data['start_time'])->addDays(1);

            $payment                               = new ChangeBoxPayment;
            $payment->change_box_id                = $request->change_box_id;
            $payment->order_detail_id              = $request->order_detail_id;
            $payment->user_id                      = $user->id;
            $payment->payment_type                 = 'midtrans';
            $payment->bank                         = null;
            $payment->amount                       = $amount;
            $payment->status_id                    = 14;
            $payment->id_name                      = $invoice;
            $payment->midtrans_url                 = $midtrans_data['redirect_url'];
            $payment->midtrans_status              = 'pending';
            $payment->midtrans_start_transaction   = $start_transaction;
            $payment->midtrans_expired_transaction = $expired_transaction;
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
            'status'  => true,
            // 'message' => 'Please wait while our admin is confirming the payment (1x24 hours).',
            'message' => 'Success submit to midtrans',
            'data'    => new ChangeBoxPaymentResource($payment)
        ]);
    }

    private function id_name()
    {
        $sql    = ChangeBoxPayment::orderBy('number', 'desc')->whereRaw("MONTH(created_at) = " . date('m'))->first(['id_name', DB::raw('substring(id_name, len(id_name)-2,len(id_name)) as number')]);
        $number = isset($sql->number) ? $sql->number : 0;
        $code   = date('ymd') . str_pad($number + 1, 3, "0", STR_PAD_LEFT);
        return $code;
    }

}