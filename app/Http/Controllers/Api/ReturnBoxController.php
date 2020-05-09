<?php

namespace App\Http\Controllers\Api;

use App\Model\ReturnBoxes;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\TransactionLog;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReturnBoxesResource;
use App\Model\Setting;
use App\Repositories\Contracts\ReturnBoxRepository;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

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
                $return->status_id              = $data['types_of_pickup_id'] == '1' ? 14 : 16;
                $return->address                = $data['address'];
                $return->order_detail_id        = $data['order_detail_id'.$a];
                $return->longitude              = $data['longitude'];
                $return->latitude               = $data['latitude'];
                $return->deliver_fee            = $data['types_of_pickup_id'] == '1' ? 50000 : 0;
                $return->save();

                //update status order detail to
                $order_detail      = OrderDetail::findOrFail($return->order_detail_id);
                if($order_detail){

                    // Transaction Log Create
                    $transactionLog = new TransactionLog;
                    $transactionLog->user_id                        = $user->id;
                    $transactionLog->transaction_type               = 'terminate';
                    $transactionLog->order_id                       = $return->id;
                    $transactionLog->status                         = $data['types_of_pickup_id'] == '1' ? 'Pend Payment' : 'Terminate Request';
                    $transactionLog->location_warehouse             = 'warehouse';
                    $transactionLog->location_pickup                = 'house';
                    $transactionLog->datetime_pickup                =  Carbon::now();
                    $transactionLog->types_of_box_space_small_id    = $order_detail->types_of_box_room_id;
                    $transactionLog->space_small_or_box_id          = $order_detail->room_or_box_id;
                    $transactionLog->amount                         = $return->deliver_fee;
                    $transactionLog->types_of_pickup_id             = $request->types_of_pickup_id;
                    $transactionLog->order_detail_id                = $order_detail_id;
                    $transactionLog->created_at                     =  Carbon::now();
                    $transactionLog->save();

                    $data1["is_returned"]        = 1;
                    $data1["status_id"]          = $data['types_of_pickup_id'] == '1' ? 14 : 16;
                    $order_detail->fill($data1)->save();

                    if($request->types_of_pickup_id != 1){
                        $client = new \GuzzleHttp\Client();
                        $response = $client->request('POST', env('APP_NOTIF') . 'api/terminate/' . $return->id, ['form_params' => [
                        'status_id'       => $return->status_id,
                        'order_detail_id' => $order_detail->id
                        ]]);
                    }

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

    public function done($order_detail_id)
    {
        try {
            $order_detail = OrderDetail::find($order_detail_id);
            if($order_detail){
                $check              = ReturnBoxes::where('order_detail_id', $order_detail_id)->first();
                if($check){
                    $data               = ReturnBoxes::find($check->id);
                    $data->status_id    = 18;
                    $data->save();

                    if($data){
                        //change status order detail
                        $order_detail->status_id       = 18;
                        $order_detail->save();
                        //change status order
                        $status_order = DB::table('orders')->where('id', $order_detail->order_id)->update(['status_id' => 18]);
                    }else{
                        return response()->json(['status' => false, 'message' => 'Return box not found.'], 401);
                    }
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

        return response()->json(['status' => true, 'message' => 'Change status success.'], 200);
    }

}
