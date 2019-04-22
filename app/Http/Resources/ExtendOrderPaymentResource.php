<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ExtendOrderPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
      $start = [
        'date'     => $this->midtrans_start_transaction,
        'timezone' => Carbon::parse($this->midtrans_start_transaction)->timezoneName
      ];

      $end = [
        'date'     => $this->midtrans_expired_transaction,
        'timezone' => Carbon::parse($this->midtrans_expired_transaction)->timezoneName
      ];
        // return parent::toArray($request);
        $data = [
          'id'                           => $this->id,
          'id_name'                      => $this->id_name,
          'extend_id'                    => $this->extend_id,
          'order_detail_id'              => $this->order_detail_id,
          'payment_date'                 => $this->created_at->format('Y-m-d H:i:s'),
          'payment_type'                 => $this->payment_type,
          'bank'                         => $this->bank,
          'midtrans_url'                 => $this->midtrans_url,
          'midtrans_status'              => $this->midtrans_status,
          'midtrans_start_transaction'   => $start,
          'midtrans_expired_transaction' => $end,
          'amount'                       => $this->amount,
          'status_id'                    => $this->status->name,
          'user'                         => new UserResource($this->user),
          'image'                        => is_null($this->image_transfer) ? null : (asset('images/payment/order/detail').'/'.$this->image_transfer),
      ];

      return $data;
    }
}
