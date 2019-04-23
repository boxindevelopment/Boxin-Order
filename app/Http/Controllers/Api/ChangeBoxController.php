<?php

namespace App\Http\Controllers\Api;

use App\Model\ChangeBox;
use App\Model\ChangeBoxDetail;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\OrderDetailBox;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChangeBoxResource;
use Illuminate\Http\Request;
use DB;
use Validator;
use Carbon\Carbon;
use Exception;

class ChangeBoxController extends Controller
{

    public function startChangeBox(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'order_detail_box_id' => 'required',
        ]);

        if($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 304);
        }

        $fee = 0;
        if ($request->has('deliver_fee')) {
          $fee = $request->deliver_fee;
        }

        DB::beginTransaction();
        try {

          $change                     = new ChangeBox;
          $change->types_of_pickup_id = $request->types_of_pickup_id;
          $change->order_detail_id    = $request->order_detail_id;
          $change->date               = $request->date;
          $change->time_pickup        = $request->time_pickup;
          $change->note               = $request->note;
          $change->status_id          = $request->types_of_pickup_id == '1' ? 14 : 19;
          $change->address            = $request->address;
          $change->deliver_fee        = $fee;
          $change->save();

          $change_id = $change->id;
          $order_details = $request->order_detail_box_id;
          for ($i=0; $i < count($order_details); $i++) { 
            ChangeBoxDetail::create([
              'change_box_id' => $change_id,
              'order_detail_box_id' => $order_details[$i]
            ]);

            $o = OrderDetailBox::find($order_details[$i]);
            if ($o) {
              $o->status_id = 19;
              $o->save();
            }
          }
          
          DB::commit();
        } catch (\Exception $th) {
          DB::rollback();          
          return response()->json([ 'status' => false, 'message' => $th->getMessage()], 401);
        }

        return response()->json(['status' => true, 'message' => 'Create request change box success.', 'data' => new ChangeBoxResource($change)]);

        // $data = $request->all();
        // if(isset($data['change_count'])) {
        //     for ($a = 1; $a <= $data['change_count']; $a++) {

        //         $validator = \Validator::make($request->all(), [
        //             'order_detail_box_id'.$a    => 'required',
        //         ]);

        //         if($validator->fails()) {
        //             return response()->json(['status' => false, 'message' => $validator->errors()], 304);
        //         }
        //     }
        // } else {
        //     return response()->json(['status' => false, 'message' => 'Not found return count.'], 401);
        // }

        // try {
        //     for ($a = 1; $a <= $data['change_count']; $a++) {
        //         $check = OrderDetailBox::find($data['order_detail_box_id'.$a]);
        //         if ($check) {
        //           $change->order_detail_box_id    = $data['order_detail_box_id'.$a];  
                    
        //             $change                         = new ChangeBox;
        //             $change->types_of_pickup_id     = $data['types_of_pickup_id'];
        //             $change->order_detail_id        = $data['order_detail_id'];   
        //             $change->date                   = $data['date'];
        //             $change->time_pickup            = $data['time_pickup'];
        //             $change->note                   = $data['note'];
        //             $change->status_id              = $data['types_of_pickup_id'] == '1' ? 14 : 19;
        //             $change->address                = $data['address'];
        //             $change->deliver_fee            = 0;
        //             $change->save();

        //             $change_detail = new ChangeBoxDetail;


        //             $data1["status_id"] = 21;  // status : not actived
        //             $order_detail_box->fill($data1)->save();
                    
        //             /*
        //             Old data
        //             //update status order detail box 
        //             $order_detail_box      = OrderDetailBox::findOrFail($data['order_detail_box_id'.$a]);
        //             if($order_detail_box){
        //                 Tidak disimpan pada order detailnya, jadi jangan update status order detailnya 
        //                 $order_detail      = OrderDetail::findOrFail($data['order_detail_id']);
        //                 if($order_detail){
        //                     // box
        //                     // 14 : pending payment
        //                     // 19 : change request
        //                     $data_orderdetail["status_id"] = $data['types_of_pickup_id'] == '1' ? 14 : 19;
        //                     $order_detail->fill($data_orderdetail)->save();
        //                 } 
        //                 // item in box
        //                 $data1["status_id"] = 21;  // status : not actived
        //                 $order_detail_box->fill($data1)->save();
        //             }
        //             */
        //         } else {
        //             return response()->json(['status' => false, 'message' => 'Not found order detail box id.'], 401);
        //         }
        //     }

        // } catch (\Exception $e) {
        //     return response()->json([ 'status' => false, 'message' => $e->getMessage()], 401);
        // }

        // return response()->json(['status' => true, 'message' => 'Create request change box success.', 'data' => new ChangeBoxResource($change)]);
    }

    public function cancelChangeBox($id)
    {
       $status = 24; // cancelled
       $change = ChangeBox::find($id);
       if (empty($change)) {
          return response()->json([ 'status' => false, 'message' => 'Data Not found!'], 422);
       }

       if ($change->status != 14 || $change->status != 19) {
          return response()->json([ 'status' => false, 'message' => 'Request can\'t cancelled.'], 422);
       }

       $order_detail_id     = $change->order_detail_id;
       $order_detail_box_id = $change->order_detail_box_id;
       $types_of_pickup_id  = $change->types_of_pickup_id;

       DB::beginTransaction();
       try {
          $odb = OrderDetailBox::findOrFail($order_detail_box_id);
          if ($odb) {
            $odb->status = 9; // fill
            $odb->save();
          }
  
          // $od = OrderDetail::findOrFail($order_detail_id);
          // if ($od) {
          //   $od->status = 20; // actived
          //   $od->save();
          // }
  
          $changebox = ChangeBox::find($id);
          $changebox->status = 24; // cancelled
          $changebox->save();

          DB::commit();
       } catch (\Exception $x) {
          DB::rollback();
          return response()->json([ 'status' => false, 'message' => $x->getMessage()], 401);
       }

       return response()->json(['status' => true, 'message' => 'Cancel request change box success.', 'data' => null]);
    }




}
