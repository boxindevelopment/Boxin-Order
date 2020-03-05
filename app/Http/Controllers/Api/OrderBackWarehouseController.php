<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Vtdirect;
use App\Http\Resources\TransactionLogResource;
use App\Model\OrderBackWarehouse;
use App\Model\TransactionLog;
use App\Repositories\Contracts\OrderDetailRepository;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;

class OrderBackWarehouseController extends Controller
{

    protected $orderDetail;

    public function __construct(OrderDetailRepository $orderDetail)
    {
        $this->orderDetail = $orderDetail;
    }

    public function store($order_detail_id, Request $request)
    {
      $user = $request->user();

      $validator = Validator::make($request->all(), [
        'types_of_pickup_id' => 'required',
        'date'               => 'required',
        'time'               => 'required',
        'address'            => 'required',
        'deliver_fee'        => 'required',
        'time_pickup'        => 'required'
      ]);

      if ($validator->fails()) {
          return response()->json([
              'status' => false,
              'message' => $validator->errors()
          ], 400);
      }

      $orderDetails = $this->orderDetail->getById($order_detail_id);
      if (count($orderDetails) < 1) {
          return response()->json([
              'status' => false,
              'message' => 'Data not found'
          ], 404);
      }
      $orderDetails = $orderDetails->first();
      if($orderDetails->status_id != 5 && $orderDetails->status_id != 7 && $orderDetails->status_id != 9 && $orderDetails->status_id != 16 && $orderDetails->status_id != 4){
          return response()->json([
              'status' => false,
              'message' => 'status failed',
              'status_transaction' => $orderDetails->status
          ], 400);
      }
      if($orderDetails->place == 'warehouse'){
          return response()->json([
              'status' => false,
              'message' => 'your box is still at warehouse'
          ], 405);
      }

      DB::beginTransaction();
      try {

        $orderDetails->place = 'house';
        if($request->types_of_pickup_id == 1){
            $orderDetails->status_id    = 14;
        } else {
            $orderDetails->status_id    = 26;
        }
        $orderDetails->save();

        $orderBackWarehouse                         = new OrderBackWarehouse;
        $orderBackWarehouse->types_of_pickup_id     = $request->types_of_pickup_id;                             // durasi inputan
        $orderBackWarehouse->order_detail_id        = $order_detail_id;
        $orderBackWarehouse->user_id                = $user->id;                            // durasi inputan
        $orderBackWarehouse->date                   = $request->date;                             // durasi inputan
        $orderBackWarehouse->time                   = $request->time;                             // durasi inputan
        $orderBackWarehouse->address                = $request->address;                             // durasi inputan
        $orderBackWarehouse->deliver_fee            = $request->deliver_fee;                              // durasi sebelumnya
        $orderBackWarehouse->time_pickup            = $request->time_pickup;
        $orderBackWarehouse->note                   = $request->note;
        if($request->types_of_pickup_id == 1){
            $orderBackWarehouse->status_id          = 14;
        } else {
            $orderBackWarehouse->status_id          = 26;
        }
        $orderBackWarehouse->save();

        // Transaction Log Create
        $transactionLog = new TransactionLog;
        $transactionLog->user_id                        = $user->id;
        $transactionLog->transaction_type               = 'back warehouse';
        $transactionLog->order_id                       = $orderBackWarehouse->id;
        if($request->types_of_pickup_id > 1){
            $transactionLog->status                         = 'Pend Payment';
        } else {
            $transactionLog->status                         = 'Return Request';
        }
        $transactionLog->location_warehouse             = 'house';
        $transactionLog->location_pickup                = 'warehouse';
        $transactionLog->datetime_pickup                =  Carbon::now();
        $transactionLog->types_of_box_space_small_id    = $orderDetails->types_of_box_room_id;
        $transactionLog->space_small_or_box_id          = $orderDetails->room_or_box_id;
        $transactionLog->amount                         = $request->deliver_fee;
        $transactionLog->created_at                     =  Carbon::now();
        $transactionLog->save();

        DB::commit();
      } catch (\Exception $x) {
        DB::rollback();
        return response()->json([
          'status' =>false,
          'message' => $x->getMessage()
        ], 422);
      }

      return response()->json([
        'status' => true,
        'message' => 'Your order has been made. Please complete the payment.',
        'data' => new TransactionLogResource($transactionLog)
      ]);
    }


    public function startPaymentBackWarehouse(Request $request)
    {
        $midtrans = new Vtdirect();
        $user = $request->user();
        $validator = \Validator::make($request->all(), [
            'order_back_warehouse_id'   => 'required',
            'amount'                    => 'required'
        ]);

        if($validator->fails()) {
          return response()->json([
            'status'  => false,
            'message' => $validator->errors()
          ]);
        }

        DB::beginTransaction();
        try {
            $orderBackWarehouse = OrderBackWarehouse::find($request->order_back_warehouse_id);
            if (!$orderBackWarehouse) {
              // throw new Exception('Order take id not found');
                  return response()->json([
                      'status' => false,
                      'message' => 'Order back warehouse id not found'
                  ], 422);
            }

            $amount = (int) $request->amount;
            $check = OrderBackWarehouse::where('order_back_warehouse_id', $request->order_back_warehouse_id)->get();
            if (count($check) > 0){
                $payment = $check->first();
              if($payment->status_id == 14){
                  return response()->json([
                      'status' => true,
                      'message' => 'Payment back warehouse created.',
                      'data' => $payment //new PaymentResource($payment)
                  ]);
              } else {
                    // throw new Exception('Order take id not found');
                    return response()->json([
                        'status' => false,
                        'message' => 'Order back warehouse payment has been paid.'
                    ], 422);
              }
              // return response()->json(['status' => false, 'message' => 'Order has been paid.'], 401);
            }

            //* data payment baru
            $invoice = 'PAY-BACK-' . $request->order_back_warehouse_id . $this->code();
            $itemID = 'BACKID-'. $request->order_back_warehouse_id;
            $info = 'Payment for order back warehouse id ' . $request->order_back_warehouse_id;
            $url_redirect = $midtrans->purchase($user, $orderBackWarehouse->created_at, $invoice, $amount, $itemID, $info);
            if (empty($url_redirect)) {
              throw new Exception('Server is busy, please try again later');
                // throw new Exception('Order take id not found');
                return response()->json([
                    'status' => false,
                    'message' => 'Server is busy, please try again later.'
                ], 422);
            }

            $start_transaction      = Carbon::parse($orderBackWarehouse->created_at);
            $expired_transaction    = Carbon::parse($orderBackWarehouse->created_at)->addDays(1);

            $orderBackWarehousePayment                               = new OrderBackWarehouse;
            $orderBackWarehousePayment->order_detail_id              = $orderBackWarehouse->order_detail_id;
            $orderBackWarehousePayment->order_back_warehouse_id      = $request->order_back_warehouse_id;
            $orderBackWarehousePayment->user_id                      = $user->id;
            $orderBackWarehousePayment->payment_type                 = 'midtrans';
            $orderBackWarehousePayment->bank                         = null;
            $orderBackWarehousePayment->amount                       = $amount;
            $orderBackWarehousePayment->status_id                    = 14;
            $orderBackWarehousePayment->midtrans_url                 = $url_redirect;
            $orderBackWarehousePayment->midtrans_status              = 'pending';
            $orderBackWarehousePayment->midtrans_start               = $start_transaction->toDateTimeString();
            $orderBackWarehousePayment->midtrans_expired             = $expired_transaction->toDateTimeString();
            $orderBackWarehousePayment->id_name                      = $invoice;
            $orderBackWarehousePayment->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 401);
        }

        return response()->json([
            'status' => true,
            // 'message' => 'Please wait while our admin is confirming the payment (1x24 hours).',
            'message' => 'Payment created.',
            'data' => $orderBackWarehousePayment //new PaymentResource($orderBackWarehousePayment)
        ]);
    }


    private function code()
    {
        $sql    = OrderBackWarehouse::orderBy('number', 'desc')->whereRaw("MONTH(created_at) = " . date('m'))->first(['id_name', DB::raw('substring(id_name, len(id_name)-2,len(id_name)) as number')]);
        $number = isset($sql->number) ? $sql->number : 0;
        $code   = date('ymd') . str_pad($number + 1, 3, "0", STR_PAD_LEFT);
        return $code;
    }

}
