<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\AddItem;
use App\Model\AddItemBox;
use App\Model\AddItemBoxPayment;
use App\Http\Resources\AddItemBoxPaymentResource;
use Auth;
use Validator;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\OrderDetailBox;
use DB;
use Carbon\Carbon;
use App\Http\Controllers\Vtdirect;
use Exception;

class AddItemBoxPaymentController extends Controller
{

  private $url;
	CONST DEV_URL = 'https://boxin-dev-notification.azurewebsites.net/';
	CONST LOC_URL = 'http://localhost:5252/';
  CONST PROD_URL = 'https://boxin-prod-notification.azurewebsites.net/';
  
  public function __construct()
  {
    $this->url = (env('DB_DATABASE') == 'coredatabase') ? self::DEV_URL : self::PROD_URL;
  }

  public function startPayment(Request $request)
  {
    $midtrans = new Vtdirect();
    $user = Auth::user();
    $validator = Validator::make($request->all(), [
        'order_detail_id' => 'required',
        'add_item_box_id' => 'required',
        'amount'          => 'required',
    ]);

    if($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ]);
    }

    DB::beginTransaction();
    try {
        $order_detail = OrderDetail::find($request->order_detail_id);
        if (!$order_detail) {
          throw new Exception("Order detail Id not found");
        }

        $additems_box = AddItemBox::find($request->add_item_box_id);
        if (!$additems_box) {
          throw new Exception("Add item Id not found");
        }

        $amount = (int) $request->amount;
        $checkPayment = AddItemBoxPayment::where('add_item_box_id', $request->add_item_box_id)->where('user_id', $user->id)->where('status_id', 14)->first();
        //* jika data sudah ada
        if ($checkPayment) {
          throw new Exception('Server is busy, please try again later');
          return response()->json([
            'status'  => true,
            'message' => 'Payment already created.',
            'data'    => $checkPayment
          ]);
        }

        //* data payment baru
        $invoice = 'PAY-ADDIT-' . $request->add_item_box_id . $this->id_name();
        $itemID = 'ADDITEMID-'. $request->add_item_box_id;
        $info = 'Payment for adding item box id ' . $request->add_item_box_id;
        $url_redirect = $midtrans->purchase($user, $additems_box->created_at, $invoice, $amount, $itemID, $info);
        if (empty($url_redirect)) {
          throw new Exception('Server is busy, please try again later');
        }

        $start_transaction = Carbon::parse($url_redirect);
        $expired_transaction = Carbon::parse($url_redirect)->addDays(1);

        $payment                               = new AddItemBoxPayment;
        $payment->add_item_box_id              = $request->add_item_box_id;
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
        'data'    => new AddItemBoxPaymentResource($payment)
    ]);
  }

  private function id_name()
  {
    $sql    = AddItemBoxPayment::orderBy('number', 'desc')->whereRaw("MONTH(created_at) = " . date('m'))->first(['id_name', DB::raw('substring(id_name, len(id_name)-2,len(id_name)) as number')]);
    $number = isset($sql->number) ? $sql->number : 0;
    $code   = date('ymd') . str_pad($number + 1, 3, "0", STR_PAD_LEFT);
    return $code;
  }


}
