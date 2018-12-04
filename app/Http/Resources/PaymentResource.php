<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'order_id'          => new Order($this->order),
            'payment_date'      => $this->created_at->format('Y-m-d H:i:s'), 
            'payment_type'      => $this->payment_type, 
            'bank'              => $this->bank, 
            'amount'            => $this->amount, 
            'status_id'         => $this->status->name,
            'user'              => new UserResource($this->user),
            'image_transfer'    => is_null($this->image_transfer) ? null : (asset('images/payment/order'),
        ];

        return $data;
    }
}
