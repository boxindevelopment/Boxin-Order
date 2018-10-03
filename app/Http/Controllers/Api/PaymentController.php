<?php

namespace App\Http\Controllers\Api;

use App\Model\Order;
use App\Model\Payment;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    
    public function startPayment(Request $request)
    {
        $user = $request->user();

        $validator = \Validator::make($request->all(), [
            'order_id'          => 'required',
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
            $id = Order::find($request->order_id);
            if($id){
                $data                    = $request->all();
                $payment                 = new Payment;
                $payment->order_id       = $request->order_id;
                $payment->user_id        = $user->id;
                $payment->payment_type   = $request->payment_type;
                $payment->payment_credit_card_id  = $request->payment_credit_card_id;
                $payment->amount         = $request->amount;
                $payment->status_id      = 5;
                $payment->save();
            }else {
                return response()->json([
                    'status' => false,
                    'message' => 'Order Id not found'
                ], 401);
            }

            
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