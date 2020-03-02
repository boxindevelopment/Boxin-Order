<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Vtdirect;
use App\Http\Resources\TransactionLogResource;
use App\Model\OrderTake;
use App\Model\OrderTakePayment;
use App\Model\TransactionLog;
use App\Repositories\Contracts\OrderDetailRepository;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;

class OrderTakeController extends Controller
{

    protected $orderDetail;

    public function __construct(OrderDetailRepository $orderDetail)
    {
        $this->orderDetail = $orderDetail;
    }

    public function take($order_detail_id, Request $request)
    {
      $user                 = $request->user();
      $message              = '';

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
          ]);
      }

      $orderDetails = $this->orderDetail->getById($order_detail_id);
      if (count($orderDetails) < 1) {
          return response()->json([
              'status' => false,
              'message' => 'Data not found'
          ]);
      }
      $orderDetails = $orderDetails->first();
      if($orderDetails->status_id != 4 && $orderDetails->status_id != 5 && $orderDetails->status_id != 7 && $orderDetails->status_id != 9){
          return response()->json([
              'status' => false,
              'message' => 'status failed'
          ]);
      }
      if($orderDetails->place == 'warehouse'){
          return response()->json([
              'status' => false,
              'message' => 'your box is still at warehouse'
          ]);
      }

      DB::beginTransaction();
      try {

        $orderDetails->place = 'warehouse';
        if($request->types_of_pickup_id == 1){
            $orderDetails->status_id          = 14;
        } else {
            $orderDetails->status_id          = 27;
        }
        $orderDetails->save();

        $orderTake                         = new OrderTake;
        $orderTake->types_of_pickup_id     = $request->types_of_pickup_id;                             // durasi inputan
        $orderTake->order_detail_id        = $order_detail_id;
        $orderTake->user_id                = $user->id;                                     // durasi inputan
        $orderTake->date                   = $request->date;                             // durasi inputan
        $orderTake->time                   = $request->time;                             // durasi inputan
        $orderTake->address                = $request->address;
        if($request->types_of_pickup_id == 1){                          // durasi inputan
            $orderTake->deliver_fee            = $request->deliver_fee;
        } else {
            $orderTake->deliver_fee            = 0;
        }                           // durasi sebelumnya
        $orderTake->time_pickup            = $request->time_pickup;
        $orderTake->note                   = $request->note;
        if($request->types_of_pickup_id == 1){
            $orderTake->status_id          = 14;
            $message                       = 'Please complete the payment.';
        } else {
            $orderTake->status_id          = 27;
        }
        $orderTake->save();

        // Transaction Log Create
        $transactionLog = new TransactionLog;
        $transactionLog->user_id                        = $user->id;
        $transactionLog->transaction_type               = 'take';
        $transactionLog->order_id                       = $orderTake->id;
        if($request->deliver_fee > 0){
            $transactionLog->status                         = 'Pend Payment';
        } else {
            $transactionLog->status                         = 'Pending';
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
        'message' => 'Your order has been made.' . $message,
        'data' => new TransactionLogResource($transactionLog)
      ]);
    }


    public function startPaymentTake(Request $request)
    {
        $midtrans = new Vtdirect();
        $user = $request->user();
        $validator = \Validator::make($request->all(), [
            'order_take_id' => 'required',
            'amount'        => 'required'
        ]);

        if($validator->fails()) {
          return response()->json([
            'status'  => false,
            'message' => $validator->errors()
          ]);
        }

        DB::beginTransaction();
        try {
            $take = OrderTake::find($request->order_take_id);
            if (!$take) {
              // throw new Exception('Order take id not found');
                  return response()->json([
                      'status' => false,
                      'message' => 'Order take id not found'
                  ], 422);
            }

            $amount = (int) $request->amount;
            $check = OrderTakePayment::where('order_take_id', $request->order_take_id)->get();
            if (count($check) > 0){
                $payment = $check->first();
              if($payment->status_id == 14){
                  return response()->json([
                      'status' => true,
                      'message' => 'Payment created.',
                      'data' => $payment //new PaymentResource($payment)
                  ]);
              } else {
                    // throw new Exception('Order take id not found');
                    return response()->json([
                        'status' => false,
                        'message' => 'Order take payment has been paid.'
                    ], 422);
              }
              // return response()->json(['status' => false, 'message' => 'Order has been paid.'], 401);
            }

            //* data payment baru
            $invoice = 'PAY-TAKE-' . $request->order_take_id . $this->code();
            $itemID = 'TAKEID-'. $request->order_take_id;
            $info = 'Payment for order take box id ' . $request->order_take_id;
            $url_redirect = $midtrans->purchase($user, $take->created_at, $invoice, $amount, $itemID, $info);
            if (empty($url_redirect)) {
              throw new Exception('Server is busy, please try again later');
                // throw new Exception('Order take id not found');
                return response()->json([
                    'status' => false,
                    'message' => 'Server is busy, please try again later.'
                ], 422);
            }

            $start_transaction = Carbon::parse($take->created_at);
            $expired_transaction = Carbon::parse($take->created_at)->addDays(1);

            $orderTakePayment                               = new OrderTakePayment;
            $orderTakePayment->order_detail_id              = $take->order_detail_id;
            $orderTakePayment->order_take_id                = $request->order_take_id;
            $orderTakePayment->user_id                      = $user->id;
            $orderTakePayment->payment_type                 = 'midtrans';
            $orderTakePayment->bank                         = null;
            $orderTakePayment->amount                       = $amount;
            $orderTakePayment->status_id                    = 14;
            $orderTakePayment->midtrans_url                 = $url_redirect;
            $orderTakePayment->midtrans_status              = 'pending';
            $orderTakePayment->midtrans_start               = $start_transaction->toDateTimeString();
            $orderTakePayment->midtrans_expired             = $expired_transaction->toDateTimeString();
            $orderTakePayment->id_name                      = $invoice;
            $orderTakePayment->save();

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
            'data' => $orderTakePayment //new PaymentResource($orderTakePayment)
        ]);
    }


    private function code()
    {
        $sql    = OrderTakePayment::orderBy('number', 'desc')->whereRaw("MONTH(created_at) = " . date('m'))->first(['id_name', DB::raw('substring(id_name, len(id_name)-2,len(id_name)) as number')]);
        $number = isset($sql->number) ? $sql->number : 0;
        $code   = date('ymd') . str_pad($number + 1, 3, "0", STR_PAD_LEFT);
        return $code;
    }

}
