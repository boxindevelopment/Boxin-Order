<?php

namespace App\Http\Controllers;

use Veritrans;
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

class Vtdirect extends Controller
{
  
    public function purchaseOrder($user, $order_created_at, $invoice, $total, $ids,  $name )
    {
      $array = [
        'start_time'   => date("Y-m-d H:i:s O", strtotime($order_created_at)),
        'redirect_url' => ''
      ];

      $transaction_details = [
          'order_id'     => $invoice,
          'gross_amount' => $total
      ];   
      $customer_details = self::customer($user); 
      $custom_expiry = self::expired($array['start_time']);
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

      $array['redirect_url'] = Veritrans::vtwebCharge($transaction_data);
      return $array;
    }

    public function checkstatus($orderId) {
      $check = Veritrans::status($orderId);
      return $check;
    }

    protected function customer($user) {
      return [
        'first_name' => $user->first_name,
        'last_name'  => $user->last_name,
        'email'      => $user->email,
        'phone'      => $user->phone
      ];
    }
    
    protected function expired($start_time) {
      return [
        'start_time' => $start_time,
        'unit'       => 'day',
        'duration'   => 1
      ];
    }

    // public function checkout_process(Request $request)
    // {
    //     $token = $request->input('token_id');
    //     $vt = new Veritrans;

    //     $transaction_details = array(
    //         'order_id'     => uniqid(),
    //         'gross_amount' => 10000
    //     );

    //     // Populate items
    //     $items = [
    //         array(
    //             'id'       => 'item1',
    //             'price'    => 5000,
    //             'quantity' => 1,
    //             'name'     => 'Adidas f50'
    //         ),
    //         array(
    //             'id'       => 'item2',
    //             'price'    => 2500,
    //             'quantity' => 2,
    //             'name'     => 'Nike N90'
    //         )
    //     ];

    //     // Populate customer's billing address
    //     $billing_address = array(
    //         'first_name'   => "Andri",
    //         'last_name'    => "Setiawan",
    //         'address'      => "Karet Belakang 15A, Setiabudi.",
    //         'city'         => "Jakarta",
    //         'postal_code'  => "51161",
    //         'phone'        => "081322311801",
    //         'country_code' => 'IDN'
    //         );

    //     // Populate customer's shipping address
    //     $shipping_address = array(
    //         'first_name'   => "John",
    //         'last_name'    => "Watson",
    //         'address'      => "Bakerstreet 221B.",
    //         'city'         => "Jakarta",
    //         'postal_code'  => "51162",
    //         'phone'        => "081322311801",
    //         'country_code' => 'IDN'
    //         );

    //     // Populate customer's Info
    //     $customer_details = array(
    //         'first_name'       => "Andri",
    //         'last_name'        => "Setiawan",
    //         'email'            => "andrisetiawan@me.com",
    //         'phone'            => "081322311801",
    //         'billing_address'  => $billing_address,
    //         'shipping_address' => $shipping_address
    //         );

    //     $transaction_data = array(
    //         'payment_type' => 'credit_card',
    //         'credit_card'  => array(
    //            'token_id' => $token,
    //            'bank'     => 'bni'
    //            ),
    //         'transaction_details' => $transaction_details,
    //         'item_details'        => $items
    //     );

        
    //     $response = null;
    //     try
    //     {
    //         $response = $vt->vtdirect_charge($transaction_data);
    //     } 
    //     catch (Exception $e) 
    //     {
    //         return $e->getMessage; 
    //     }

    //     //var_dump($response);
    //     if($response)
    //     {
    //         if($response->transaction_status == "capture")
    //         {
    //             //success
    //             echo "Transaksi berhasil. <br />";
    //             echo "Status transaksi untuk order id ".$response->order_id.": ".$response->transaction_status;

    //             echo "<h3>Detail transaksi:</h3>";
    //             var_dump($response);
    //         }
    //         else if($response->transaction_status == "deny")
    //         {
    //             //deny
    //             echo "Transaksi ditolak. <br />";
    //             echo "Status transaksi untuk order id ".$response->order_id.": ".$response->transaction_status;

    //             echo "<h3>Detail transaksi:</h3>";
    //             var_dump($response);
    //         }
    //         else if($response->transaction_status == "challenge")
    //         {
    //             //challenge
    //             echo "Transaksi challenge. <br />";
    //             echo "Status transaksi untuk order id ".$response->order_id.": ".$response->transaction_status;

    //             echo "<h3>Detail transaksi:</h3>";
    //             var_dump($response);
    //         }
    //         else
    //         {
    //             //error
    //             echo "Terjadi kesalahan pada data transaksi yang dikirim.<br />";
    //             echo "Status message: [".$response->status_code."] ".$response->status_message;

    //             echo "<h3>Response:</h3>";
    //             var_dump($response);
    //         }   
    //     }

    // }

}    