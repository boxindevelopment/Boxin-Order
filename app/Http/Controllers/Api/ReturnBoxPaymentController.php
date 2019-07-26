<?php

namespace App\Http\Controllers\Api;

use App\Model\OrderDetail;
use App\Model\ReturnBoxPayment;
use App\Model\ReturnBoxes;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReturnBoxPaymentResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Vtdirect;
use DB;
use Carbon\Carbon;
use Exception;

class ReturnBoxPaymentController extends Controller
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
    //         $order_detail = OrderDetail::find($request->order_detail_id);
    //         if($order_detail){
    //             $check = ReturnBoxPayment::where('order_detail_id', $request->order_detail_id)->get();
    //             if(count($check)>0){
    //                 return response()->json(['status' => false, 'message' => 'Order return box has been paid.'], 401);
    //             }
    //             $data                    = $request->all();
    //             $payment                 = new ReturnBoxPayment;
    //             $payment->order_detail_id= $request->order_detail_id;
    //             $payment->user_id        = $user->id;
    //             $payment->payment_type   = 'transfer';
    //             $payment->bank           = $request->bank;
    //             $payment->amount         = $request->amount;
    //             $payment->status_id      = 15;
    //             if ($request->hasFile('image')) {
    //                 if ($request->file('image')->isValid()) {
    //                     $getimageName = time().'.'.$request->image->getClientOriginalExtension();
    //                     $image = $request->image->move(public_path('images/payment/return'), $getimageName);
            
    //                 }
    //             }
    //             $payment->image_transfer = $getimageName;
    //             $payment->id_name        = 'PAYRB'.$this->id_name();
    //             $payment->save();

    //             if($payment){
    //                 //change status order detail
    //                 $order_detail->status_id       = 15;
    //                 $order_detail->save();
    //                 //change status order
    //                 $status_order = DB::table('orders')->where('id', $order_detail->order_id)->update(['status_id' => 15]);
    //             }
    //         }else {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Order Detail Id not found'
    //             ], 401);
    //         }
            
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ]);
    //     }

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Create data return box payment success.',
    //         'data' => new ReturnBoxPaymentResource($payment->fresh())
    //     ]);
    // }
    
    public function startPayment(Request $request)
    {
        $midtrans = new Vtdirect();
        $user = $request->user();
        $validator = \Validator::make($request->all(), [
            'order_detail_id' => 'required',
            'amount'          => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $order_detail = OrderDetail::find($request->order_detail_id);
        if (!$order_detail) {
          return response()->json([
            'status' => false,
            'message' => 'Order detail id not found'
          ], 422);
          // throw new Exception("Order detail id not found");
        }

        //? check return box
        $returnbox = ReturnBoxes::where('order_detail_id', $request->order_detail_id)->where('status_id', 14)->first();
        if (empty($returnbox)) {
          return response()->json([
            'status' => false,
            'message' => 'Return box id not found'
          ], 422);
          // throw new Exception("Return box id not found");
        }

        DB::beginTransaction();
        try {
            $amount = (int) $request->amount;
            $checkPayment = ReturnBoxPayment::where('order_detail_id', $request->order_detail_id)->where('user_id', $user->id)->where('status_id', 14)->first();
            //* jika data sudah ada
            if ($checkPayment) {
              // throw new Exception('Return box has been paid.');
              return response()->json([
                'status'  => true,
                'message' => 'Payment already created.',
                'data'    => $checkPayment
              ]);
            }

            //* data payment baru
            $invoice       = 'PAY-RTBOX-' . $request->order_detail_id . $this->id_name();
            $itemID        = 'RETURNBOXID-'. $request->order_detail_id;
            $info          = 'Payment for return box id ' . $request->order_detail_id;
            $url_redirect = $midtrans->purchase($user, $returnbox->created_at, $invoice, $amount, $itemID, $info);
            if (empty($url_redirect)) {
              throw new Exception('Server is busy, please try again later');
            }

            $start_transaction = Carbon::parse($url_redirect);
            $expired_transaction = Carbon::parse($url_redirect)->addDays(1);

            $payment                               = new ReturnBoxPayment;
            $payment->order_detail_id              = $request->order_detail_id;
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
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 401);
        }

      return response()->json([
        'status' => true,
        'message' => 'Success submit to midtrans',
        'data' => new ReturnBoxPaymentResource($payment)
      ]);
    }

    private function id_name()
    {
        $sql    = ReturnBoxPayment::orderBy('number', 'desc')->whereRaw("MONTH(created_at) = " . date('m'))->first(['id_name', DB::raw('substring(id_name, len(id_name)-2,len(id_name)) as number')]);
        $number = isset($sql->number) ? $sql->number : 0;
        $code   = date('ymd') . str_pad($number + 1, 3, "0", STR_PAD_LEFT);
        return $code;
    }

}