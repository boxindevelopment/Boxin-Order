<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReturnBoxPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $data = [
            'id'                           => $this->id,
            'id_name'                      => $this->id_name,
            'order_detail_id'              => new OrderDetailResource($this->order_detail),
            'payment_date'                 => $this->created_at->format('Y-m-d H:i:s'),
            'payment_type'                 => $this->payment_type,
            'bank'                         => $this->bank,
            'midtrans_url'                 => $this->midtrans_url,
            'midtrans_status'              => $this->midtrans_status,
            'midtrans_start_transaction'   => $this->midtrans_start_transaction,
            'midtrans_expired_transaction' => $this->midtrans_expired_transaction,
            'amount'                       => $this->amount,
            'status_id'                    => $this->status->name,
            'user'                         => new UserResource($this->user),
            'image'                        => is_null($this->image_transfer) ? null : (asset('images/payment/return').'/'.$this->image_transfer),
        ];

        return $data;
    }
}
