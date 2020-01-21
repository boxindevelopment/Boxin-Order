<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderTakeResource extends JsonResource
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
            'id'                     => $this->id,
            'date'                   => $this->date,
            'time'                   => $this->time,
            'address'                => $this->address,
            'deliver_fee'            => $this->deliver_fee,
            'time_pickup'            => $this->time_pickup,
            'note'                   => $this->note,
            'created_at'             => $this->created_at,
            'user'                   => new UserResource($this->user),
            'order_detail'           => new OrderDetailResource($this->order_detail),
            'type_pickup'            => new TypePickUpResource($this->type_pickup),
            'status'                 => $this->status
        ];

        return $data;
    }
}
