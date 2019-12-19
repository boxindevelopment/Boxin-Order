<?php

namespace App\Http\Controllers;


use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\OrderDetailBox;
use App\Model\ChangeBox;
use App\Model\User;
use App\Model\SpaceSmall;
use App\Model\Box;
use App\Model\ExtendOrderDetail;
use App\Model\DeliveryFee;
use App\Model\Price;
// use Veritrans;
use App\Veritrans\Veritrans;

class Vtdirect extends Controller
{

    public function __construct()
    {
      // Merchant ID	: G642466093
      // Client Key : SB-Mid-client-YSOTFjf0lq_E3LyO
      // Server Key : SB-Mid-server-8EPMaViFTi3UFeVE3vjuHDH4
      Veritrans::$serverKey = config('midtrans.server_key');
      Veritrans::$isProduction = config('midtrans.is_production');
    }

    public function purchase($user, $order_created_at, $invoice, $total, $ids, $name)
    {
      $expr = date("Y-m-d H:i:s O", strtotime($order_created_at));
      $transaction_details = [
          'order_id'     => $invoice,
          'gross_amount' => $total
      ];
      $customer_details = self::customer($user);
      $custom_expiry = self::expired($expr);
      $item_details = [
          'id'            => $ids,
          'quantity'      => 1,
          'name'          => $name,
          'price'         => $total,
          'merchant_name' => 'Box-in'
      ];

      $transaction_data = [
          'payment_type'        => 'vtweb',
          'transaction_details' => $transaction_details,
          'item_details'        => $item_details,
          'customer_details'    => $customer_details,
          'expiry'              => $custom_expiry
      ];

      $redirect_url = Veritrans::vtweb_charge($transaction_data);
      if (empty($redirect_url)) {
        return "";
      }

      return $redirect_url;
    }

    public function checkStatus($orderId) {
      $check = Veritrans::status($orderId);
      if (is_null($check)) {
        return array();
      }

      return (array) $check;
    }

    private function customer($user) {
      return [
        'first_name' => $user->first_name,
        'last_name'  => $user->last_name,
        'email'      => $user->email,
        'phone'      => $user->phone
      ];
    }

    private function expired($start_time) {
      return [
        'start_time' => $start_time,
        'unit'       => 'day',
        'duration'   => 1
      ];
    }

}
