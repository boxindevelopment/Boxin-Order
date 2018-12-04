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
            'id'                => $this->id,
            'order_detail_id'  => new OrderDetail($this->order_detail),
            'payment_date'      => $this->created_at->format('Y-m-d H:i:s'), 
            'payment_type'      => $this->payment_type, 
            'bank'              => $this->bank, 
            'amount'            => $this->amount, 
            'status_id'         => $this->status->name,
            'user'              => new UserResource($this->user),
            'image'             => is_null($this->image_transfer) ? null : (asset('images/payment/return').'/'.$this->image_transfer),
        ];

        return $data;
    }
}
