<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReturnBoxesResource extends JsonResource
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
            'order_detail_id'   => $this->order_detail_id,
            'address'           => $this->address,
            'latitude'          => $this->latitude,
            'longitude'         => $this->longitude,
            'date'              => $this->date,
            'time'              => $this->time,
            'note'              => $this->note,
            'deliver_fee'       => $this->deliver_fee,
            'driver_name'       => $this->driver_name,
            'driver_phone'      => $this->driver_phone,
            'created_date'      => $this->created_at->format('Y-m-d'),
            'status'            => $this->status->name,
            'type_pickup'       => new TypePickUpResource($this->type_pickup),
        ];

        return $data;
    }
}
