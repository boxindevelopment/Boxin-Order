<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PickupOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (!is_null($this->order_id)) {
            $order = [
                'id'        => $this->order->id,
                'total'     => $this->order->total,
            ];
        }

        if (!is_null($this->order->user_id)) {
            $user = [
                'id'        => $this->order->user->id,
                'first_name'=> $this->order->user->first_name,
                'last_name' => $this->order->user->last_name,
                'email'     => $this->order->user->email,
                'phone'     => $this->order->user->phone,
            ];
        }

        if (!is_null($this->types_of_pickup_id)) {
            $type_pickup = [
                'id'    => $this->type_pickup->id,
                'name'  => $this->type_pickup->name,
            ];
        }

        $data = [
            'id'                => $this->id,
            'address'           => $this->address,
            'latitude'          => $this->latitude,
            'longitude'         => $this->longitude,
            'date'              => $this->date,
            'time'              => $this->time,
            'note'              => $this->note,
            'pickup_fee'        => $this->pickup_fee,
            'driver_name'       => $this->driver_name,
            'driver_phone'      => $this->driver_phone,
            'user'              => $user,
            'order_date'        => $this->order->created_at->format('Y-m-d'),
            'created_date'      => $this->created_at->format('Y-m-d'),
            'status'            => $this->status->name,
            'type_pickup'       => new TypePickUpResource($this->type_pickup),
        ];

        return $data;
    }
}
