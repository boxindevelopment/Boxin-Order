<?php

namespace App\Http\Controllers\Api;

use App\Model\ReturnBoxes;
use App\Model\OrderDetail;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReturnBoxesResource;
use Illuminate\Http\Request;
use DB;
use App\Model\Setting;
use App\Repositories\Contracts\ReturnBoxRepository;

class ReturnBoxController extends Controller
{
    protected $repository;

    public function __construct(ReturnBoxRepository $repository)
    {
        $this->repository = $repository;
    }

    public function startReturnBox(Request $request)
    {
        $user = $request->user();
        
        $validator = \Validator::make($request->all(), [
            'return_count'      => 'required',
        ]);

        if($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 304);
        }

        $data = $request->all();
        if(isset($data['return_count'])) {
            for ($a = 1; $a <= $data['return_count']; $a++) {

                $validator = \Validator::make($request->all(), [
                    'order_detail_id'.$a    => 'required',
                ]);

                if($validator->fails()) {
                    return response()->json(['status' => false, 'message' => $validator->errors()], 304);
                }
            }
        } else {
            return response()->json(['status' =>false, 'message' => 'Not found return count.'], 401);
        }

        try {
            for ($a = 1; $a <= $data['return_count']; $a++) {
                $return                         = new ReturnBoxes;
                $return->types_of_pickup_id     = $data['types_of_pickup_id'];
                $return->date                   = $data['date'];
                $return->time                   = $data['time'];
                $return->time_pickup            = $data['time_pickup'];
                $return->note                   = $data['note'];
                $return->status_id              = 11;
                $return->address                = $data['address'];
                $return->order_detail_id        = $data['order_detail_id'.$a];
                $return->longitude              = $data['longitude'];
                $return->latitude               = $data['latitude'];        
                $return->deliver_fee            = 0;
                $return->save();

                //update status order detail to
                $order      = OrderDetail::findOrFail($return->order_detail_id);
                if($order){
                    $data1["is_returned"]        = 1;       
                    $order->fill($data1)->save();
                }
            }

        } catch (\Exception $e) {
            return response()->json([ 'status' =>false, 'message' => $e->getMessage()], 401);
        }

        return response()->json(['status' => true, 'message' => 'Create return box success.', 'data' => new ReturnBoxesResource($return)]);

    }

    public function my_deliveries(Request $request)
    {
        $user   = $request->user();
        $params = array();
        $params['user_id'] = $user->id;
        $params['limit']   = intval($request->limit);
        $data  = $this->repository->findPaginate($params);
        
        if($data) {
            foreach ($data as $k => $v) {
                $data[$k] = $v->toSearchableArray();
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Data not found.'], 301);
        }

        return response()->json($data);
    }

}
