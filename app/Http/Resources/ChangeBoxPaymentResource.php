<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChangeBoxPaymentResource extends JsonResource
{
    public function toArray($request)
    {

        $data = [
            'id'                => $this->id,            
            'id_name'           => $this->id_name,
            'order_detail_id'   => $this->order_detail_id,
            'payment_date'      => $this->created_at->format('Y-m-d H:i:s'), 
            'payment_type'      => $this->payment_type, 
            'bank'              => $this->bank, 
            'amount'            => $this->amount, 
            'status_id'         => new StatusResource($this->status),
            'user'              => new UserResource($this->user),
            'image'             => is_null($this->image_transfer) ? null : (asset('images/payment/changebox').'/'.$this->image_transfer),
        ];

        return $data;
    }
}
