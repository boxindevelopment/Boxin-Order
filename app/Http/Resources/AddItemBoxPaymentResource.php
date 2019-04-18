<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddItemBoxPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        $data = [
            'id'                           => $this->id,
            'id_name'                      => $this->id_name,
            'order_detail_id'              => $this->order_detail_id,
            'add_item_box_id'              => $this->add_item_box_id,
            'payment_date'                 => $this->created_at->format('Y-m-d H:i:s'),
            'payment_type'                 => $this->payment_type,
            'bank'                         => $this->bank,
            'midtrans_url'                 => $this->midtrans_url,
            'midtrans_status'              => $this->midtrans_status,
            'midtrans_start_transaction'   => $this->midtrans_start_transaction,
            'midtrans_expired_transaction' => $this->midtrans_expired_transaction,
            'amount'                       => $this->amount,
            'status_id'                    => new StatusResource($this->status),
            'user'                         => new UserResource($this->user),
            'image'                        => $this->image
        ];

        return $data;
    }
}
