<?php

namespace App\Http\Controllers\Api;

use App\Model\OrderDetail;
use App\Model\ReturnBoxPayment;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReturnBoxPaymentResource;
use Illuminate\Http\Request;

class ReturnBoxPaymentController extends Controller
{
    
    public function startPayment(Request $request)
    {
        $user = $request->user();

        $validator = \Validator::make($request->all(), [
            'order_detail_id'   => 'required',
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
            $id = OrderDetail::find($request->order_detail_id);
            if($id){
                $data                    = $request->all();
                $payment                 = new ReturnBoxPayment;
                $payment->order_detail_id= $request->order_detail_id;
                $payment->user_id        = $user->id;
                $payment->payment_type   = 'transfer';
                $payment->bank           = $request->bank;
                $payment->amount         = $request->amount;
                $payment->status_id      = 15;
                if ($request->hasFile('image')) {
                    if ($request->file('image')->isValid()) {
                        $getimageName = time().'.'.$request->image->getClientOriginalExtension();
                        $image = $request->image->move(public_path('images/payment/return'), $getimageName);
            
                    }
                }
                $payment->image_transfer = $getimageName;
                $payment->save();
            }else {
                return response()->json([
                    'status' => false,
                    'message' => 'Order Detail Id not found'
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
            'data' => new ReturnBoxPaymentResource($payment->fresh())
        ]);
    }


}