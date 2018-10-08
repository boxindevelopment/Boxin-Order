<?php

namespace App\Http\Controllers\Api;

use App\Model\ReturnBoxes;
use App\Model\Order;
use App\Model\Room;
use App\Model\Box;
use App\Model\OrderDetail;
use App\Model\Price;
use App\Model\PickupOrder;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReturnBoxesResource;
use Illuminate\Http\Request;
use DB;
use App\Model\Setting;
use App\Repositories\Contracts\WarehouseRepository;

class ReturnBoxController extends Controller
{
    protected $warehouse;

    public function __construct(WarehouseRepository $warehouse)
    {
        $this->warehouse = $warehouse;
    }

    public function startReturnBox(Request $request)
    {

        $user = $request->user();
        
        $validator = \Validator::make($request->all(), [
            'return_count'      => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        $data = $request->all();
        if(isset($data['return_count'])) {
            for ($a = 1; $a <= $data['return_count']; $a++) {

                $validator = \Validator::make($request->all(), [
                    'order_detail_id'.$a    => 'required',
                    // 'types_of_pickup_id'.$a => 'required',
                    // 'date'.$a               => 'required',
                    // 'time'.$a               => 'required',
                ]);

                if($validator->fails()) {
                    return response()->json([
                        'status' => false,
                        'message' => $validator->errors()
                    ]);
                }
            }
        } else {
            return response()->json([
                'status' =>false,
                'message' => 'Not found return count.'
            ], 401);
        }

        try {
            for ($a = 1; $a <= $data['return_count']; $a++) {
                $return                         = new ReturnBoxes;
                $return->types_of_pickup_id     = $data['types_of_pickup_id'];
                $return->date                   = $data['date'];
                $return->time                   = $data['time'];
                $return->note                   = $data['note'];
                $return->status_id              = 11;
                $return->address                = $data['address'];
                $return->order_detail_id        = $data['order_detail_id'.$a];
                $return->longitude              = $data['longitude'];
                $return->latitude               = $data['latitude'];        
                $return->deliver_fee            = 0;
                $return->save();

                //update status order detail to
                DB::table('order_details')->where('id', $return->order_detail_id)->update(['status_id' => 11]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' =>false,
                'message' => $e->getMessage()
            ], 401);
        }

        return response()->json([
            'status' => true,
            'message' => 'Create return box success.',
            'data' => new ReturnBoxesResource($return)
        ]);

    }

}
