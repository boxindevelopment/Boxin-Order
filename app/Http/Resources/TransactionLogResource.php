<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionLogResource extends JsonResource
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
            'id'                            => $this->id,
            'user_id'                       => $this->user_id,
            'transaction_type'              => $this->transaction_type,
            'order_id'                      => $this->order_id,
            'status'                        => $this->status,
            'location_warehouse'            => $this->location_warehouse,
            'location_pickup'               => $this->location_pickup,
            'datetime_pickup'               => $this->datetime_pickup,
            'types_of_box_space_small_id'   => $this->types_of_box_space_small_id,
            'space_small_or_box_id'         => $this->space_small_or_box_id,
            'amount'                        => $this->amount,
            'created_at'                    => $this->created_at
        ];

        return $data;
    }
}
