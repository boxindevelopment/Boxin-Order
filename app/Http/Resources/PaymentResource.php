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
            'date_time'         => $this->date_time, 
            'payment_type'      => $this->payment_type, 
            'payment_credit_card_id' => $this->payment_credit_card_id, 
            'amount'            => $this->amount, 
            'status_payment'    => $this->status_payment, 
            'status_id'         => $this->status->name,
            'user'              => new UserResource($this->user),
        ];

        return $data;
    }
}
