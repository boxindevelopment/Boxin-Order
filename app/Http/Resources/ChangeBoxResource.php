<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChangeBoxResource extends JsonResource
{

    public function toArray($request)
    {

        $data = [
            'id'                    => $this->id,
            'order_detail_id'       => $this->order_detail_id,
            'order_detail_box_id'   => $this->order_detail_box_id,
            'address'               => $this->address,
            'date'                  => $this->date,
            'time_pickup'           => $this->time_pickup,
            'deliver_fee'           => $this->deliver_fee,
            'driver_name'           => $this->driver_name,
            'driver_phone'          => $this->driver_phone,
            'created_date'          => $this->created_at->format('Y-m-d'),
            'status'                => $this->status->name,
            'type_pickup'           => new TypePickUpResource($this->type_pickup),
        ];

        return $data;
    }
}
