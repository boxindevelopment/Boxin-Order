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
        if (!is_null($this->space_id)) {
            $space = [
                'id'    => $this->space->id,
                'name'  => $this->space->name,
            ];
        }

        $data = [
            'id'                => $this->id,
            'total'             => $this->total,
            'order_date'        => $this->created_at->format('Y-m-d'),
            'user'              => new UserResource($this->user),
            'status'            => $this->status->name,
            'area'              => new AreaResource($this->space),
            'order_detail'      => $this->order_detail,
            'pickup_order'      => $this->pickup_order,
        ];

        return $data;
    }
}
