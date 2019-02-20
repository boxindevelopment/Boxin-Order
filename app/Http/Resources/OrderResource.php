<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'total'                  => $this->total,
            'order_date'             => $this->created_at->format('Y-m-d'),
            'user'                   => new UserResource($this->user),
            'status'                 => $this->status->name,
            'area'                   => new AreaResource($this->area),
            'order_detail'           => new OrderDetailResource($this->order_detail),
            'pickup_order'           => $this->pickup_order,
            'payment_expired'        => $this->payment_expired,
            'payment_status_expired' => $this->payment_status_expired
        ];

        return $data;
    }
}
