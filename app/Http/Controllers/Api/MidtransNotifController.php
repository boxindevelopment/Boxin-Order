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
      echo 'notification handler';
      $json_result = file_get_contents('php://input');
      $result      = json_decode($json_result);

      if ($result) {
        $notif = $vt->status($result->order_id);
      }
      error_log(print_r($result,TRUE));

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
            }
        }
      }
      else if ($transaction == 'settlement') {
        // TODO set payment status in merchant's database to 'Settlement'
        echo "Transaction order_id: " . $order_id ." successfully transfered using " . $type;

      } 
      else if ($transaction == 'pending'){
        // TODO set payment status in merchant's database to 'Pending'
        // echo "Waiting customer to finish transaction order_id: " . $order_id . " using " . $type;
        echo "Waiting customer to finish transaction.";
      } 
      else if ($transaction == 'deny') {
        // TODO set payment status in merchant's database to 'Denied'
        
        echo "Payment using " . $type . " for transaction order_id: " . $order_id . " is denied.";
      }
      else if ($transaction == 'expire') {
        // TODO set payment status in merchant's database to 'Expired'

        echo "Payment using " . $type . " for transaction order_id: " . $order_id . " is expired.";
      }
  
  }

  protected function setPayment($order_id, $type, $status) 
  {
    

  }

  protected function getInvoiceString($string)
  {
    // === order id ===
    // PAY-ADDIT
    // PAY-CHBOX
    // PAY-RTBOX
    // PAY-XTEND
    // PAY-ORDER

    $vars = $string
  }

}
