<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddItemBoxResource extends JsonResource
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
        $data = [
            'id'                 => $this->id,
            'order_detail_id'    => $this->order_detail_id,
            'types_of_pickup_id' => $this->types_of_pickup_id,
            'address'            => $this->address,
            'date'               => $this->date,
            'note'               => $this->note,
            'time_pickup'        => $this->time_pickup,
            'deliver_fee'        => $this->deliver_fee,
            'driver_name'        => $this->driver_name,
            'driver_phone'       => $this->driver_phone,
            'created_date'       => $this->created_at->format('Y-m-d'),
            'status'             => $this->status->name,
            'type_pickup'        => new TypePickUpResource($this->type_pickup),
            'items'              => AddItemResource::collection($this->items)
        ];

        return $data;
    }
}
