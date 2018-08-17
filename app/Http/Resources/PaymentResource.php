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
            'payment_date'      => $this->created_at->format('Y-m-d H:i:s'), 
            'payment_type'      => $this->payment_type, 
            'payment_credit_card_id' => $this->payment_credit_card_id, 
            'amount'            => $this->amount, 
            'status_id'         => $this->status->name,
            'user'              => new UserResource($this->user),
        ];

        return $data;
    }
}
