<?php

namespace App\Http\Controllers\Api;

use App\Model\Order;
use App\Model\Payment;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\Request;
use Midtrans;

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

    public function purchase() {
        $transaction_details = [
            'order_id' => time(),
            'gross_amount' => 10000
        ];
        
        $customer_details = [
            'first_name' => 'User',
            'email' => 'user@gmail.com',
            'phone' => '08238493894'
        ];
        
        $custom_expiry = [
            'start_time' => date("Y-m-d H:i:s O", time()),
            'unit' => 'day',
            'duration' => 2
        ];
        
        $item_details = [
            'id' => 'PROD-1',
            'quantity' => 1,
            'name' => 'Product-1',
            'price' => 10000
        ];

        // Send this options if you use 3Ds in credit card request
        $credit_card_option = [
            'secure' => true, 
            'channel' => 'migs'
        ];

        $transaction_data = [
            'transaction_details' => $transaction_details,
            'item_details' => $item_details,
            'customer_details' => $customer_details,
            'expiry' => $custom_expiry,
            'credit_card' => $credit_card_option,
        ];

        $token = Midtrans::getSnapToken($transaction_data);
        return $token;
    }
}