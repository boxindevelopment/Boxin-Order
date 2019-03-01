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
    public function startPayment(Request $request)
    {
        $user = $request->user();

        $validator = \Validator::make($request->all(), [
            'order_detail_id'   => 'required',
            'change_box_id'   => 'required',
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
            $change_box_id = ChangeBox::find($request->change_box_id);
            if ($change_box_id){
                $check = ChangeBoxPayment::where('change_box_id', $request->change_box_id)->where('status_id', '7')->get();
                if (count($check) > 0) {
                    return response()->json(['status' => false, 'message' => 'Request has been paid.'], 401);
                } else {
                    $data                     = $request->all();
                    $payment                  = new ChangeBoxPayment;
                    $payment->order_detail_id = $request->order_detail_id;
                    $payment->user_id         = $user->id;
                    $payment->payment_type    = 'transfer';
                    $payment->bank            = $request->bank;
                    $payment->amount          = $request->amount;
                    $payment->status_id       = 15;
                    $payment->change_box_id   = $request->change_box_id;
                    
                    if ($request->hasFile('image')) {
                        if ($request->file('image')->isValid()) {
                            $getimageName = time().'.'.$request->image->getClientOriginalExtension();
                            $image = $request->image->move(public_path('images/payment/changebox'), $getimageName);
                
                        }
                    }
                    $payment->image_transfer = $getimageName;
                    $payment->id_name        = 'PAYCB'.$this->id_name();
                    $payment->save();

                    // if($payment){
                    //     //change status order detail
                    //     $order_detail->status_id       = 15;
                    //     $order_detail->save();                        
                    // }
                }
                
            }else {
                return response()->json(['status' => false, 'message' => 'Order detail Id not found'], 401);
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
            'data' => new ChangeBoxPaymentResource($payment)
        ]);
    }

    private function id_name()
    {

        $sql    = ChangeBoxPayment::orderBy('number', 'desc')->whereRaw("MONTH(created_at) = " . date('m'))->first(['id_name', DB::raw('substring(id_name,12,14) as number')]);
        $number = isset($sql->number) ? $sql->number : 0;
        $code   = date('ymd') . str_pad($number + 1, 3, "0", STR_PAD_LEFT);

        return $code;

    }

}