<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExtendOrderDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
      
      return [
        'id'                     => $this->id,
        'order_detail_id'        => $this->order_detail_id,
        'order_id'               => $this->order_id,
        'extend_duration'        => $this->extend_duration,
        'remaining_duration'     => $this->remaining_duration,
        'amount'                 => $this->amount,
        'end_date_before'        => $this->end_date_before,
        'payment_expired'        => $this->payment_expired,
        'payment_status_expired' => $this->payment_status_expired,
        'user_id'                => $this->user_id,
        'new_end_date'           => $this->new_end_date,
        'new_duration'           => $this->new_duration,
        'total_amount'           => $this->total_amount,
        'status'                 => new StatusResource($this->status),
        // 'order'                  => $this->order,
        // 'order_detail'           => $this->order_detail
      ];

    }
}
