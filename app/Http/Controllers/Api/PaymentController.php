<?php

namespace App\Http\Controllers\Api;

use App\Model\Order;
use App\Model\Payment;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentController extends Controller
{
    
    public function startPayment(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'order_id'          => 'required',
            'user_id'           => 'required',
            'amount'            => 'required',
            'payment_type'      => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        try {

            $data                    = $request->all();
            $payment                 = new Payment;
            $payment->order_id       = $request->order_id;
            $payment->user_id        = $request->user_id;
            $payment->date_time      = Carbon::now()->toDateString();
            $payment->payment_type   = $request->payment_type;
            $payment->payment_credit_card_id  = $request->payment_credit_card_id;
            $payment->amount         = $request->amount;
            $payment->status_id      = 4;
            $payment->save();
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Create data payment success.',
            'data' => new PaymentResource($payment->fresh())
        ]);
    }


}