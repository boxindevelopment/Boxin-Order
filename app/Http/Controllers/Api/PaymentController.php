<?php

namespace App\Http\Controllers\Api;

use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\Payment;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\Request;
use DB;

class PaymentController extends Controller
{
    public function startPayment(Request $request)
    {
        $user = $request->user();

        $validator = \Validator::make($request->all(), [
            'order_id'          => 'required',
            'amount'            => 'required',
            'bank'              => 'required',
            'image'             => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        try {
            $order = Order::find($request->order_id);
            if($order){
                $check = Payment::where('order_id', $request->order_id)->get();
                if(count($check)>0){
                    return response()->json(['status' => false, 'message' => 'Order has been paid.'], 401);
                }
                $data                    = $request->all();
                $payment                 = new Payment;
                $payment->order_id       = $request->order_id;
                $payment->user_id        = $user->id;
                $payment->payment_type   = 'transfer';
                $payment->bank           = $request->bank;
                $payment->amount         = $request->amount;
                $payment->status_id      = 15;
                if ($request->hasFile('image')) {
                    if ($request->file('image')->isValid()) {
                        $getimageName = time().'.'.$request->image->getClientOriginalExtension();
                        $image = $request->image->move(public_path('images/payment/order'), $getimageName);
            
                    }
                }
                $payment->image_transfer = $getimageName;
                $payment->id_name        = 'PAY'.$this->id_name();
                $payment->save();

                if($payment){
                    //change status order
                    $order->status_id       = 15;
                    $order->save();
                    //change status order detail
                    $status_order = DB::table('order_details')->where('order_id', $request->order_id)->update(['status_id' => 15]);
                }
                
            }else {
                return response()->json(['status' => false, 'message' => 'Order Id not found'], 401);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Please wait while our admin is confirming the payment (1x24 hours).',
            'data' => new PaymentResource($payment->fresh())
        ]);
    }

    private function id_name()
    {

        $sql    = Payment::orderBy('number', 'desc')->whereRaw("MONTH(created_at) = " . date('m'))->first(['id_name', DB::raw('substring(id_name,8,10) as number')]);
        $number = isset($sql->number) ? $sql->number : 0;
        $code   = date('ym') . str_pad($number + 1, 3, "0", STR_PAD_LEFT);

        return $code;

    }

}