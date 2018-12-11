<?php

namespace App\Http\Controllers\Api;

use App\Model\ChangeBox;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\OrderDetailBox;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChangeBoxResource;
use Illuminate\Http\Request;
use DB;

class ChangeBoxController extends Controller
{

    public function startChangeBox(Request $request)
    {
        $user = $request->user();
        $validator = \Validator::make($request->all(), [
            'change_count'      => 'required',
        ]);

        if($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 304);
        }

        $data = $request->all();
        if(isset($data['change_count'])) {
            for ($a = 1; $a <= $data['change_count']; $a++) {

                $validator = \Validator::make($request->all(), [
                    'order_detail_box_id'.$a    => 'required',
                ]);

                if($validator->fails()) {
                    return response()->json(['status' => false, 'message' => $validator->errors()], 304);
                }
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Not found return count.'], 401);
        }

        try {
            for ($a = 1; $a <= $data['change_count']; $a++) {
                $check = OrderDetailBox::find($data['order_detail_box_id'.$a]);
                if($check){
                    $change                         = new ChangeBox;
                    $change->types_of_pickup_id     = $data['types_of_pickup_id'];
                    $change->order_detail_id        = $data['order_detail_id'];   
                    $change->order_detail_box_id    = $data['order_detail_box_id'.$a];     
                    $change->date                   = $data['date'];
                    $change->time_pickup            = $data['time_pickup'];
                    $change->note                   = $data['note'];
                    $change->status_id              = $data['types_of_pickup_id'] == '1' ? 14 : 19;
                    $change->address                = $data['address'];
                    $change->deliver_fee            = 0;
                    $change->save();

                    //update status order detail box 
                    $order_detail_box      = OrderDetailBox::findOrFail($data['order_detail_box_id'.$a]);
                    if($order_detail_box){
                        $order_detail      = OrderDetail::findOrFail($data['order_detail_id']);
                        if($order_detail){
                            $data_orderdetail["status_id"] = $data['types_of_pickup_id'] == '1' ? 14 : 19;
                            $order_detail->fill($data_orderdetail)->save();
                        }
                        $data1["status_id"]          = 21;  
                        $order_detail_box->fill($data1)->save();
                    }
                }else{
                    return response()->json(['status' => false, 'message' => 'Not found order detail box id.'], 401);
                }
            }

        } catch (\Exception $e) {
            return response()->json([ 'status' => false, 'message' => $e->getMessage()], 401);
        }

        return response()->json(['status' => true, 'message' => 'Create request change box success.', 'data' => new ChangeBoxResource($change)]);
    }

}
