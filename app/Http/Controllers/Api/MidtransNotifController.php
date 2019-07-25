<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Vtdirect;
use App\Model\Payment;
use App\Model\Box;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\SpaceSmall;
use App\Model\PickupOrder;
use App\Model\UserDevice;
use App\Model\AddItem;
use App\Model\AddItemBox;
use App\Model\AddItemBoxPayment;
use App\Model\ChangeBox;
use App\Model\ChangeBoxPayment;
use App\Model\ReturnBoxPayment;
use App\Model\ReturnBox;
use App\Model\ExtendOrderDetail;
use App\Model\ExtendOrderPayment;
use Veritrans;
use DB;
use Exception;

class MidtransNotifController extends Controller
{
  private $url;
	CONST DEV_URL  = 'https://boxin-dev-notification.azurewebsites.net/';
	CONST LOC_URL  = 'http://localhost:5252/';
	CONST PROD_URL = 'https://boxin-prod-notification.azurewebsites.net/';
  
  public function __construct()
  {
    $this->url = (env('DB_DATABASE') == 'coredatabase') ? self::DEV_URL : self::PROD_URL;
  }

  public function notification()
  {
      // === order id ===
      // PAY-ADDIT
      // PAY-CHBOX
      // PAY-RTBOX
      // PAY-XTEND
      // PAY-ORDER
      $vt = new Veritrans;
      // echo 'notification handler';
      $json_result = file_get_contents('php://input');
      $result      = json_decode($json_result);

      $notif = null;
      if ($result) {
        $notif = $vt->status($result->order_id);
      } 
      
      if (empty($notif)) {
        error_log(print_r($result,TRUE));
        abort(404);
      }

      $transaction = $notif->transaction_status;
      $type        = $notif->payment_type;
      $order_id    = $notif->order_id;
      $fraud       = $notif->fraud_status;

      if ($transaction == 'capture') {
        // For credit card transaction, we need to check whether transaction is challenge by FDS or not
        if ($type == 'credit_card') {
          if ($fraud == 'challenge') {
              // TODO set payment status in merchant's database to 'Challenge by FDS'
              // TODO merchant should decide whether this transaction is authorized or not in MAP
              echo "Transaction order_id: " . $order_id ." is challenged by FDS";
            } 
            else {
              // TODO set payment status in merchant's database to 'Success'
              echo "Transaction order_id: " . $order_id ." successfully captured using " . $type;
              self::setPayment($order_id, $type, $transaction, $fraud);
            }
        }
      }
      else if ($transaction == 'settlement') {
        // TODO set payment status in merchant's database to 'Settlement'
        echo "Transaction order_id: " . $order_id ." successfully transfered using " . $type;
        self::setPayment($order_id, $type, $transaction, $fraud);
      } 
      else if ($transaction == 'pending'){
        // TODO set payment status in merchant's database to 'Pending'
        // echo "Waiting customer to finish transaction order_id: " . $order_id . " using " . $type;
        echo "Waiting customer to finish transaction.";
      } 
      else if ($transaction == 'deny') {
        // TODO set payment status in merchant's database to 'Denied'
        self::setPayment($order_id, $type, $transaction, $fraud);
        echo "Payment using " . $type . " for transaction order_id: " . $order_id . " is denied.";
      }
      else if ($transaction == 'expire') {
        // TODO set payment status in merchant's database to 'Expired'
        self::setPayment($order_id, $type, $transaction, $fraud);
        echo "Payment using " . $type . " for transaction order_id: " . $order_id . " is expired.";
      }
  
  }

  protected function setPayment($order_id, $type, $status, $fraud) 
  {
    $arrString = self::getInvoiceString($order_id);
    $type_db   = $arrString['tipe'];
    $id_db     = $arrString['id'];
    switch ($type_db) {
      case 'ADDIT':
        self::setAdditem($id_db, $type, $status, $fraud);
        break;

      case 'CHBOX':
        # code...
        self::setChangeBox($id_db, $type, $status, $fraud);
        break;

      case 'RTBOX':
        # code...
        self::setReturnBox($id_db, $type, $status, $fraud);
        break;

      case 'XTEND':
        # code...
        self::setExtendOrder($id_db, $type, $status, $fraud);
        break;

      case 'ORDER':
        # code...
        self::setOrder($id_db, $type, $status, $fraud);
        break;
      
      default:
        # code...
        break;
    }
  }

  protected function getInvoiceString($string)
  {
    // === order id ===
    // PAY-ADDIT2-
    // PAY-CHBOX2-
    // PAY-RTBOX2-
    // PAY-XTEND2-
    // PAY-ORDER2-
    try {
      $arrays = explode("-",$str);
      $invoice = $arrays[1];
      $panjang_string = strlen($invoice);
      $type_payment = substr($invoice, 0, 5);
      $id = (int)substr($invoice, 5, $panjang_string);

      return [
        'tipe' => $type_payment,
        'id'   => $id
      ];
    } catch (\Exception $th) {
      return [];
    }
  }

  protected function setAdditem($id, $type, $status, $fraud)
  {
    $checkPayment = AddItemBoxPayment::find($id);
    if ($checkPayment) {
      $additems_box = AddItemBox::find($checkPayment->add_item_box_id);
      if ($checkPayment->status_id != 5 || $checkPayment->status_id != 6) {
        $checkPayment->midtrans_status = $status;
        $checkPayment->payment_type    = $type;
        $checkPayment->save();

        if ($status == 'pending') {

        } else  if ($status == 'settlement' || $status == 'success') {
            // status code 5 = success
            $checkPayment->status_id = 5;
            $checkPayment->save();

            if ($additems_box) {
              //change status on table add_item
              $additems_box->status_id = 5;
              $additems_box->save();
            }
        } else {
          if ($status == 'capture') {
            if ($fraud != 'challenge') {
              // status code 5 = success
              $checkPayment->status_id = 5;
              $checkPayment->save();

              if ($additems_box) {
                //change status on table add_item
                $additems_box->status_id = 5;
                $additems_box->save();
              }
            }
          } else {
            // sementara challenge = failed
            $checkPayment->status_id = 6;
            $checkPayment->save();
            // status code 6 = failed
            //change status on table add_item
            if ($additems_box) {
              //change status on table add_item
              $additems_box->status_id = 6;
              $additems_box->save();
            }
          }
        }
      }
    }
  }
  
  protected function setChangeBox($id, $type, $status, $fraud)
  {
    $checkPayment = ChangeBoxPayment::find($id);
    if ($checkPayment) {
      $chbox = ChangeBox::find($checkPayment->change_box_id);
      if ($checkPayment->status_id != 5 || $checkPayment->status_id != 6) {
        $checkPayment->midtrans_status = $status;
        $checkPayment->payment_type    = $type;
        $checkPayment->save();

        if ($status == 'pending') {

        } else  if ($status == 'settlement' || $status == 'success') {
            // status code 5 = success
            $checkPayment->status_id = 5;
            $checkPayment->save();

            if ($chbox) {
              //change status on table add_item
              $chbox->status_id = 5;
              $chbox->save();
            }
        } else {
          if ($status == 'capture') {
            if ($fraud != 'challenge') {
               // status code 5 = success
                $checkPayment->status_id = 5;
                $checkPayment->save();

                if ($chbox) {
                  //change status on table add_item
                  $chbox->status_id = 5;
                  $chbox->save();
                }
            }
          } else {
            $checkPayment->status_id = 6;
            $checkPayment->save();
            // status code 6 = failed
            //change status on table add_item
            if ($chbox) {
              //change status on table add_item
              $chbox->status_id = 6;
              $chbox->save();
            } 
          }
        }
      }
    }
  }
  
  protected function setReturnBox($id, $type, $status, $fraud)
  {
    $checkPayment = ReturnBoxPayment::find($id);
    if ($checkPayment) {
      $returnbox = ReturnBox::where('order_detail_id', $checkPayment->order_detail_id)->where('user_id', $checkPayment->user_id)->first();
      if ($checkPayment->status_id != 5 || $checkPayment->status_id != 6) {
        $checkPayment->midtrans_status = $status;
        $checkPayment->payment_type    = $type;
        $checkPayment->save();

        if ($status == 'pending') {

        } else  if ($status == 'settlement' || $status == 'success') {
            // status code 5 = success
            $checkPayment->status_id = 5;
            $checkPayment->save();

            if ($returnbox) {
              //change status on table add_item
              $returnbox->status_id = 5;
              $returnbox->save();
            }
        } else {
          if ($status == 'capture') {
            if ($fraud != 'challenge') {
               // status code 5 = success
                $checkPayment->status_id = 5;
                $checkPayment->save();

                if ($returnbox) {
                  //change status on table add_item
                  $returnbox->status_id = 5;
                  $returnbox->save();
                }
            }
          } else {
            $checkPayment->status_id = 6;
            $checkPayment->save();
            // status code 6 = failed
            //change status on table add_item
            if ($returnbox) {
              //change status on table add_item
              $returnbox->status_id = 6;
              $returnbox->save();
            }
          }
        }
      }
    }
  }
  
  protected function setExtendOrder($id, $type, $status, $fraud)
  {
    $checkPayment = ExtendOrderPayment::find($id);
    if ($checkPayment) {
      if ($checkPayment->status_id != 5 || $checkPayment->status_id != 6) {
        $checkPayment->midtrans_status = $status;
        $checkPayment->payment_type    = $type;
        $checkPayment->save();

        if ($status == 'pending') {

        } else  if ($status == 'settlement' || $status == 'success') {
            // status code 5 = success
            $checkPayment->status_id = 5;
            $checkPayment->save();
            self::paymentStatusExtend($checkPayment->extend_id, 5);
        } else {
          if ($status == 'capture') {
            if ($fraud != 'challenge') {
               // status code 5 = success
                $checkPayment->status_id = 5;
                $checkPayment->save();
                self::paymentStatusExtend($checkPayment->extend_id, 5);
            }
          } else {
            $checkPayment->status_id = 6;
            $checkPayment->save();
            // status code 6 = failed
            self::paymentStatusExtend($checkPayment->extend_id, 6);
          }
           
        }
      }
    }
  }

  protected function paymentStatusExtend($extend_id, $status)
  {
      /**
       * status:
       * 
       * 8 = reject
       * 7 = Approved
       * 5 = success
       * 6 = failed
       * 11 = pending
       * 14 = pend payment
       * 15 = confirming
       * 
       */
      $ex_order_details = ExtendOrderDetail::find($extend_id);
      if ($ex_order_details) {
          $ex_order_details->status_id = intval($status);
          $ex_order_details->save();

          if ($status == 5) {
              $orderDetails           = OrderDetail::findOrFail($ex_order_details->order_detail_id);
              $orderDetails->amount   = $ex_order_details->total_amount;                              // total amount dari durasi baru dan lama
              $orderDetails->end_date = $ex_order_details->new_end_date;                              // durasi tanggal berakhir yang baru
              $orderDetails->duration = $ex_order_details->new_duration;                              // total durasi
              $orderDetails->save();
          }

          if ($status == 5 || $status == 6){
            $params['status_id'] =  $status;
            $params['order_detail_id'] = $ex_order_details->order_detail_id;
            $user_id = $ex_order_details->user_id;
            $userDevice = UserDevice::where('user_id', $user_id)->get();
            if(count($userDevice) > 0){
                // $response = Requests::post($this->url . 'api/confirm-payment/' . $user_id, [], $params, []);
              $client = new \GuzzleHttp\Client();
              $response = $client->request('POST', $this->url . 'api/confirm-payment/' . $order->user_id, ['form_params' => [
                'status_id'       => $status,
                'order_detail_id' => $ex_order_details->order_detail_id
              ]]);
            }
          }
      }
  }

  protected function setOrder($id, $type, $status, $fraud)
  {
    $checkPayment = Payment::find($id);
    if ($checkPayment) {
      if ($checkPayment->status_id != 5 || $checkPayment->status_id != 6) {
        $checkPayment->midtrans_status = $status;
        $checkPayment->payment_type    = $type;
        $checkPayment->save();

        if ($status == 'pending') {

        } else  if ($status == 'settlement' || $status == 'success') {
            // status code 5 = success
            $checkPayment->status_id = 5;
            $checkPayment->save();
            self::paymentStatusOrder($checkPayment->order_id, 5);

        } else {
          if ($status == 'capture') {
            if ($fraud != 'challenge') {
               // status code 5 = success
                $checkPayment->status_id = 5;
                $checkPayment->save();
                self::paymentStatusOrder($checkPayment->order_id, 5);
            }
          } else {
            $checkPayment->status_id = 6;
            $checkPayment->save();
            // status code 6 = failed
            self::paymentStatusOrder($checkPayment->order_id, 6);
          }
        }
      }
    }
  }




  protected function paymentStatusOrder($order_id, $status) {
    /**
     * status:
     * 
     * 8 = reject
     * 7 = Approved
     * 5 = success
     * 6 = failed
     * 11 = pending
     * 14 = pend payment
     * 15 = confirming
     * 
     */
    $order            = Order::find($order_id);
    $order->status_id = $status;
    $order->save();

    $po            = PickupOrder::where('order_id', $order_id)->first();
    $po->status_id = $status;
    $po->save();

    $array = array();
    $order_details = OrderDetail::where('order_id', $order_id)->get();
    foreach ($order_details as $key => $value) {
      $array[] = array(
        'room_or_box_id'       => $value->room_or_box_id,
        'types_of_box_room_id' => $value->types_of_box_room_id
      );
      $value->status_id = $status;
      $value->save();
    }

    if ($status == 6) {
      for ($i=0; $i < count($array); $i++) { 
        self::backToEmpty($array[$i]['types_of_box_room_id'], $array[$i]['room_or_box_id']);
      }
    }

    foreach ($order_details as $key => $value) {
      if ($status == 5 || $status == 6){
        $params['status_id']       = $status;
        $params['order_detail_id'] = $value->id;
        $userDevice = UserDevice::where('user_id', $order->user_id)->get();
        if(count($userDevice) > 0){
          $client = new \GuzzleHttp\Client();
          $response = $client->request('POST', $this->url . 'api/confirm-payment/' . $order->user_id, ['form_params' => [
            'status_id'       => $status,
            'order_detail_id' => $value->id
          ]]);
        }
      }
    }
  }

  protected function backToEmpty($types_of_box_room_id, $id)
  {
    if ($types_of_box_room_id == 1 || $types_of_box_room_id == "1") {
      // order box
      $box = Box::find($id);
      if ($box) {
        $box->status_id = 10;
        $box->save();
      }
    }
    else if ($types_of_box_room_id == 2 || $types_of_box_room_id == "2") {
      // order room
      $box = SpaceSmall::find($id);
      if ($box) {
        $box->status_id = 10;
        $box->save();
      }
    }
  }

  public function finish()
  {
    return view('thankyou');
  }

}
