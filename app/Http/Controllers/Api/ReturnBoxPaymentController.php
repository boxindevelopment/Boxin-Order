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
            $order_detail = OrderDetail::find($request->order_detail_id);
            if($order_detail){
                $check = ReturnBoxPayment::where('return_boxes_id', $request->return_boxes_id)->get();
                if(count($check)>0){
                    return response()->json(['status' => false, 'message' => 'Order return box has been paid.'], 401);
                }
                $data                    = $request->all();
                $payment                 = new ReturnBoxPayment;
                $payment->return_boxes_id= $request->return_boxes_id;
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
                $payment->id_name        = 'PAYRB'.$this->id_name();
                $payment->save();

                if($payment){
                    //change status order detail
                    $order_detail->status_id       = 15;
                    $order_detail->save();
                    //change status order
                    $status_order = DB::table('orders')->where('id', $order_detail->order_id)->update(['status_id' => 15]);
                }
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
            'message' => 'Create data return box payment success.',
            'data' => new ReturnBoxPaymentResource($payment->fresh())
        ]);
    }

    private function id_name()
    {

        $sql    = ReturnBoxPayment::orderBy('number', 'desc')->whereRaw("MONTH(created_at) = " . date('m'))->first(['id_name', DB::raw('substring(id_name,10,12) as number')]);
        $number = isset($sql->number) ? $sql->number : 0;
        $code   = date('ym') . str_pad($number + 1, 3, "0", STR_PAD_LEFT);

        return $code;

    }

}