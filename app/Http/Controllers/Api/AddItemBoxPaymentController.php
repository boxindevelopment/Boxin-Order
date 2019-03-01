<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\AddItem;
use App\Model\AddItemBox;
use App\Model\AddItemBoxPayment;
use Auth;
use Validator;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\OrderDetailBox;
use DB;
use Carbon\Carbon;

class AddItemBoxPaymentController extends Controller
{

  public function startPayment(Request $request)
  {
    $user = Auth::user();
    $validator = Validator::make($request->all(), [
        'order_detail_id' => 'required',
        'add_item_box_id' => 'required',
        'amount'          => 'required',
        'bank'            => 'required',
        'image'           => 'required',
    ]);

    if($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ]);
    }

    $payment = null;
    try {
        $order_detail = OrderDetail::find($request->order_detail_id);
        if ($order_detail) {
            $check = AddItemBoxPayment::where('add_item_box_id', $request->add_item_box_id)->where('user_id', $user->id)->where('status_id', '7')->get();
            if (count($check) > 0){
                return response()->json(['status' => false, 'message' => 'Request has been paid.'], 401);
            } else {
                $payment                  = new AddItemBoxPayment;
                $payment->order_detail_id = $request->order_detail_id;
                $payment->user_id         = $user->id;
                $payment->payment_type    = 'transfer';
                $payment->bank            = $request->bank;
                $payment->amount          = $request->amount;
                $payment->status_id       = 15;
                $getimageName = '';
                if ($request->hasFile('image')) {
                    if ($request->file('image')->isValid()) {
                        $getimageName = time().'.'.$request->image->getClientOriginalExtension();
                        $image = $request->image->move(public_path('images/payment/changebox'), $getimageName);

                    }
                }
                $payment->image_transfer = $getimageName;
                $payment->id_name        = 'PAYCB'. $this->id_name();
                $payment->save();

                $add_item = AddItemBox::find($request->add_item_box_id);
                if ($add_item) {
                  $add_item->status_id = 15;
                  $add_item->save();
                }
            }

        } else {
            return response()->json(['status' => false, 'message' => 'Order detail Id not found'], 401);
        }

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ]);
    }

    return response()->json([
        'status'  => true,
        'message' => 'Please wait while our admin is confirming the payment (1x24 hours).',
        'data'    => new AddItemBoxPaymentResource($payment)
    ]);
  }

  private function id_name()
  {
    $sql    = AddItemBoxPayment::orderBy('number', 'desc')->whereRaw("MONTH(created_at) = " . date('m'))->first(['id_name', DB::raw('substring(id_name,12,14) as number')]);
    $number = isset($sql->number) ? $sql->number : 0;
    $code   = date('ymd') . str_pad($number + 1, 3, "0", STR_PAD_LEFT);
    return $code;

  }


}
