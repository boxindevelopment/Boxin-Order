<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

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
        $start = [
          'date'     => $this->midtrans_start_transaction,
          'timezone' => Carbon::parse($this->midtrans_start_transaction)->timezoneName
        ];

        $end = [
          'date'     => $this->midtrans_expired_transaction,
          'timezone' => Carbon::parse($this->midtrans_expired_transaction)->timezoneName
        ];

        $data = [
            'id'                           => $this->id,
            'id_name'                      => $this->id_name,
            'order_detail_id'              => $this->order_detail_id,
            'add_item_box_id'              => $this->add_item_box_id,
            'payment_date'                 => $this->created_at->format('Y-m-d H:i:s'),
            'payment_type'                 => $this->payment_type,
            'bank'                         => $this->bank,
            'midtrans_url'                 => $this->midtrans_url,
            'midtrans_start_transaction'   => $start,
            'midtrans_expired_transaction' => $end,
            'amount'                       => $this->amount,
            'status_id'                    => new StatusResource($this->status),
            'user'                         => new UserResource($this->user),
            'image'                        => $this->image
        ];

        return $data;
    }
}
