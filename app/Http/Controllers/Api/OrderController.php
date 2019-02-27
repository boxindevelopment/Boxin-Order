<?php

namespace App\Http\Controllers\Api;

use App\Model\Order;
use App\Model\SpaceSmall;
use App\Model\Box;
use App\Model\OrderDetail;
use App\Model\ExtendOrderDetail;
use App\Model\DeliveryFee;
use App\Model\Price;
use App\Model\PickupOrder;
use App\Jobs\MessageInvoice;
use App\Http\Controllers\Controller;
use App\Http\Resources\BoxResource;
use App\Http\Resources\SpaceResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PriceResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Repositories\Contracts\BoxRepository;
use App\Repositories\Contracts\SpaceSmallRepository;
use App\Repositories\Contracts\PriceRepository;
use DB;
use PDF;
use Exception;

class OrderController extends Controller
{
    protected $spaceSmall;
    protected $boxes;
    protected $price;

    private $url;
    CONST DEV_URL = 'https://boxin-dev-notification.azurewebsites.net/';
    CONST LOC_URL = 'http://localhost:5252/';
    CONST PROD_URL = 'https://boxin-prod-notification.azurewebsites.net/';

    public function __construct(BoxRepository $boxes, SpaceSmallRepository $spaceSmall, PriceRepository $price)
    {
        $this->boxes      = $boxes;
        $this->spaceSmall = $spaceSmall;
        $this->price      = $price;
        $this->url        = (env('DB_DATABASE') == 'coredatabase') ? self::DEV_URL : self::PROD_URL;
    }

    public function chooseProduct($area_id)
    {
        $choose1 = $this->price->getChooseProduct(1, 2, $area_id);
        $choose2 = $this->price->getChooseProduct(2, 2, $area_id);

        $arr1           = array();
        $arr1['name']   = ($choose1) ? $choose1->name : null;
        $arr1['min']    = ($choose1) ? intval($choose1->min) : 0;
        $arr1['max']    = ($choose1) ? intval($choose1->max) : 0;
        $arr1['time']   = ($choose1) ? $choose1->alias  : null;
        $arr1['type_of_box_room_id'] = 1;

        $arr2 = array();
        $arr2['name']   = ($choose2) ? $choose2->name : null;
        $arr2['min']    = ($choose2) ? intval($choose2->min) : 0;
        $arr2['max']    = ($choose2) ? intval($choose2->max) : 0;
        $arr2['time']   = ($choose2) ? $choose2->alias : null;
        $arr2['type_of_box_room_id'] = 2;

        if($choose1) {
            return response()->json([
                'status'    => true,
                'data_box'  => $arr1,
                'data_room' => $arr2
            ]);
        }

        return response()->json(['status' => false, 'message' => 'Data not found']);
    }

    public function checkOrder($types_of_box_room_id, $area_id, $types_of_size_id)
    {
        if($types_of_box_room_id == 1) {
            $check = $this->boxes->getData(['status_id' => 10, 'area_id' => $area_id, 'types_of_size_id' => $types_of_size_id]);
        } else if ($types_of_box_room_id == 2) {
            $totalSpace = $this->spaceSmall->getData(['status_id' => 10, 'area_id' => $area_id, 'types_of_size_id' => $types_of_size_id]);
            if(count($totalSpace) > 0){
                $check = $totalSpace;
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Kapasitas penuh, Anda dapat menyewa di...'
                ]);
            }
        }

        if(count($check) > 0) {
            if($types_of_box_room_id == 1) {
                $data = BoxResource::collection($check);
            } else if ($types_of_box_room_id == 2) {
                $data = SpaceResource::collection($check);
            }
            return response()->json([
                'status'    => true,
                'data'      => $data
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Kapasitas penuh, Anda dapat menyewa di...'
        ]);
    }

    public function listAvailable($types_of_box_room_id, $types_of_size_id, $city_id)
    {
        if($types_of_box_room_id == 1) {
            $check = $this->boxes->getAvailable($types_of_size_id, $city_id);
        } else if ($types_of_box_room_id == 2) {
            $checkBoxInSpace = $this->spaceSmall->anyBoxInSpace();
            if(count($checkBoxInSpace) > 0){
                $check = $this->spaceSmall->getAvailable($types_of_size_id, $city_id);
            }
        }

        if(count($check) > 0) {
            if($types_of_box_room_id == 1) {
                $data = BoxResource::collection($check);
            } else if ($types_of_box_room_id == 2) {
                $data = SpaceResource::collection($check);
            }
            return response()->json([
                'status'    => true,
                'data'      => $data
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data not found'
        ]);
    }

    public function getOrder($id)
    {
        $order = Order::find($id);

        if($order){
            return response()->json([
                'status' => true,
                'data' => new OrderResource($order)
            ]);
        }


        return response()->json([
            'status' => false,
            'message' => 'Data not found'
        ]);

    }

    public function startStoring(Request $request)
    {
        $user = $request->user();

        $validator = \Validator::make($request->all(), [
            'area_id'           => 'required|exists:areas,id',
            'order_count'       => 'required',
            'types_of_pickup_id'=> 'required',
            'date'              => 'required',
            'time'              => 'required',
            'pickup_fee'        => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        $data = $request->all();
        if(isset($data['order_count'])) {
            for ($a = 1; $a <= $data['order_count']; $a++) {

                $validator = \Validator::make($request->all(), [
                    'types_of_size_id'.$a => 'required',
                    'types_of_box_room_id'.$a => 'required',
                    'types_of_duration_id'.$a => 'required',
                    'duration'.$a => 'required',
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
                'message' => 'Not found order count.'
            ], 401);
        }


        DB::beginTransaction();
        try {
            $order                         = new Order;
            $order->user_id                = $user->id;
            $order->payment_expired        = Carbon::now()->addDays(1)->toDateTimeString();
            $order->payment_status_expired = 0;
            $order->area_id                = $request->area_id;
            $order->status_id              = 14;
            $order->total                  = 0;
            $order->qty                    = $data['order_count'];
            $order->save();

            $pickup                 = new PickupOrder;
            $pickup->date           = $request->date;

            $amount = 0;
            $total = 0;
            $total_amount = 0;
            $id_name = '';

            for ($a = 1; $a <= $data['order_count']; $a++) {
                $order_detail                         = new OrderDetail;
                $order_detail->order_id               = $order->id;
                $order_detail->status_id              = 14;
                $order_detail->types_of_duration_id   = $data['types_of_duration_id'.$a];
                $order_detail->types_of_box_room_id   = $data['types_of_box_room_id'.$a];
                $order_detail->types_of_size_id       = $data['types_of_size_id'.$a];
                $order_detail->duration               = $data['duration'.$a];
                $order_detail->start_date             = $pickup->date;

                // weekly
                if ($order_detail->types_of_duration_id == 2 || $order_detail->types_of_duration_id == '2') {
                    $end_date                   = $order_detail->duration*7;
                    $order_detail->end_date     = date('Y-m-d', strtotime('+'.$end_date.' days', strtotime($order_detail->start_date)));
                }
                // monthly
                else if ($order_detail->types_of_duration_id == 3 || $order_detail->types_of_duration_id == '3') {
                    $order_detail->end_date     = date('Y-m-d', strtotime('+'.$order_detail->duration.' month', strtotime($order_detail->start_date)));
                }
                // 6month
                else if ($order_detail->types_of_duration_id == 7 || $order_detail->types_of_duration_id == '7') {
                    $end_date                   = $order_detail->duration*6;
                    $order_detail->end_date     = date('Y-m-d', strtotime('+'.$end_date.' month', strtotime($order_detail->start_date)));
                }
                // annual
                else if ($order_detail->types_of_duration_id == 8 || $order_detail->types_of_duration_id == '8') {
                    $end_date                   = $order_detail->duration*12;
                    $order_detail->end_date     = date('Y-m-d', strtotime('+'.$end_date.' month', strtotime($order_detail->start_date)));
                }


                // order box
                if ($order_detail->types_of_box_room_id == 1 || $order_detail->types_of_box_room_id == "1") {
                    $type = 'box';

                    // get box
                    $boxes = $this->boxes->getData(['status_id' => 10, 'area_id' => $request->area_id, 'types_of_size_id' => $data['types_of_size_id'.$a]]);
                    if(isset($boxes[0]->id)){
                        $id_name = $boxes[0]->id_name;
                        $room_or_box_id = $boxes[0]->id;
                        //change status box to fill
                        DB::table('boxes')->where('id', $room_or_box_id)->update(['status_id' => 9]);
                    } else {
                        throw new Exception('The box is not available.');
                        // return response()->json(['status' => false, 'message' => 'The box is not available.']);
                    }

                    // get price box
                    $price = $this->price->getPrice($order_detail->types_of_box_room_id, $order_detail->types_of_size_id, $order_detail->types_of_duration_id, $order->area_id);

                    if($price){
                        $amount = $price->price*$order_detail->duration;
                    }else{
                        // change status room to empty when order failed to create
                        Box::where('id', $room_or_box_id)->update(['status_id' => 10]);
                        throw new Exception('Not found price box.');
                        // return response()->json(['status' => false, 'message' => 'Not found price box.']);
                    }
                }

                // order room
                if ($order_detail->types_of_box_room_id == 2 || $order_detail->types_of_box_room_id == "2") {
                    $type = 'space';
                    // get space small
                    $spaceSmall = $this->spaceSmall->getData(['status_id' => 10, 'area_id' => $request->area_id, 'types_of_size_id' => $data['types_of_size_id'.$a]]);
                    if(!empty($spaceSmall->id)){
                        $code_space_small = $spaceSmall->code_space_small;
                        $room_or_box_id = $spaceSmall->id;
                        //change status room to fill
                        SpaceSmall::where('id', $room_or_box_id)->update(['status_id' => 9]);
                    }else{
                        // change status room to empty when order failed to create
                        throw new Exception('The room is not available.');
                        // return response()->json(['status' => false, 'message' => 'The room is not available.']);
                    }

                    // get price room
                    $price = $this->price->getPrice($order_detail->types_of_box_room_id, $order_detail->types_of_size_id, $order_detail->types_of_duration_id, $order->area_id);

                    if($price){
                        $amount = $price->price*$order_detail->duration;
                    }else{
                        // change status room to empty when order failed to create
                        SpaceSmall::where('id', $room_or_box_id)->update(['status_id' => 10]);
                        throw new Exception('Not found price room.');
                        // return response()->json([
                        //     'status' =>false,
                        //     'message' => 'Not found price room.'
                        // ], 401);
                    }
                }

                $order_detail->name           = 'New '. $type .' '. $a;
                $order_detail->room_or_box_id = $room_or_box_id;
                $order_detail->amount         = $amount;
                $order_detail->id_name        = date('Ymd') . $order->id;

                $total += $order_detail->amount;
                $order_detail->save();

                // if($order_detail->save()){
                //     $find      = OrderDetail::findOrFail($order_detail->id);
                //     if($find){
                //         $update["id_name"]           = $code_space_small.$order_detail->id;
                //         $find->fill($update)->save();
                //     }
                // }
            }

            $pickup->order_id       = $order->id;
            $pickup->types_of_pickup_id = $request->types_of_pickup_id;
            $pickup->address        = $request->address;
            $pickup->longitude      = $request->longitude;
            $pickup->latitude       = $request->latitude;
            $pickup->time           = $request->time;
            $pickup->time_pickup    = $request->time_pickup;
            $pickup->note           = $request->note;
            $pickup->pickup_fee     = $request->pickup_fee;
            $pickup->status_id      = 14;
            $pickup->save();

            //update total order
            $total_amount += $total;
            if($request->types_of_pickup_id == 1){
                $total_all = $total_amount + intval($request->pickup_fee);
            } else {
                $total_all = $total_amount;
            }

            //voucher
            if(strtoupper($request->voucher) == 'DIBOXININAJA'){
                    $tot = $total_all - (0.1 * $total_all);
            }else{
                $tot = $total_all;
            }

            Order::where('id', $order->id)->update(['total' => $tot, 'deliver_fee' => intval($request->pickup_fee)]);

            $order = Order::with('order_detail.type_size', 'payment')->findOrFail($order->id);
            // MessageInvoice::dispatch($order, $user)->onQueue('processing');
            // $response = Requests::post($this->url . 'api/payment-email/' . $order->id, [], $params, []);
            DB::commit();
        } catch (Exception $e) {
            // delete order when order_detail failed to create
            // Order::where('id', $order->id)->delete();
            DB::rollback();
            return response()->json([
                'status' =>false,
                'message' => $e->getMessage()
            ], 401);
        }

        return response()->json([
            'status' => true,
            'message' => 'Your order has been made. Please complete the payment within 2 hours.',
            'data' => new OrderResource($order)
        ]);

    }

    public function update(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'order_detail_id'   => 'required',
            'name'              => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        try {
            $id         = $request->order_detail_id;
            $order      = OrderDetail::findOrFail($id);
            $data       = $request->all();
            if($order){
                $data["name"]           = $request->name;
                $order->fill($data)->save();
            }

        } catch (\Exception $e) {

            return response()->json([
                'status' =>false,
                'message' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Update name order detail success.',
            'data' => $order
        ]);

    }

    public function extend(Request $request)
    {
        $user = $request->user();

        $validator = \Validator::make($request->all(), [
            'order_id'           => 'required|exists:order,id',
            'types_of_pickup_id'=> 'required',
            'date'              => 'required',
            'time'              => 'required',
        ]);

    }

    public function cancelOrder($id, Request $request)
    {

        $order  = Order::find($id);
        $status = 24;
        if($order){
            $user = $request->user();
            if($user->id != $order->user_id){
                return response()->json([
                    'status' => false,
                    'message' => "Order can't canceled, you're not ordering of this order"
                ]);
            }
            if($order->status_id == 7 || $order->status_id == 14){
                $order->status_id   = $status;
                $order->save();
                DB::table('pickup_orders')->where('order_id', $order->id)->update(['status_id' => $status]);
                DB::table('order_details')->where('order_id', $order->id)->update(['status_id' => $status]);

                $ods = DB::table('order_details')->where('order_id', $order->id)->pluck('room_or_box_id')->toArray();
                if ($ods) {
                  DB::table('boxes')->whereIn('id', $ods)->update(['status_id' => 10]);
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Update status to cancelled success.',
                    'data' => $order
                ]);
            }

        }


        return response()->json([
            'status' => false,
            'message' => "Order can't canceled"
        ]);

    }

    public function checkExpiredOrder()
    {
        try {
            Order::whereDate('payment_expired', '=', Carbon::now()->toDateTimeString())
                    ->where('payment_status_expired', '=', 0)
                    ->update([
                        'payment_status_expired' => 1,
                        'status_id'              => 8
                    ]);

            ExtendOrderDetail::whereDate('payment_expired', '=', Carbon::now()->toDateTimeString())
                    ->where('payment_status_expired', '=', 0)
                    ->update([
                        'payment_status_expired' => 1,
                        'status_id'              => 8
                    ]);
            return response()->json([
                'status'  => true,
                'message' => 'success'
            ]);
        } catch (Exception $x) {
            return response()->json([
                'status' => false,
                'message' => $x->getMessage()
            ]);
        }
        // Order::whereDate('payment_expired', '=', Carbon::now()->toDateTimeString())->get();
    }

}
