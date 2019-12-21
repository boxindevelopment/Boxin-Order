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
        $boxOrSmallSpaceFee     = ($this->transaction_type == 'start storing') ? $this->order->order_detail->sum('amount') : 0;
        $voucherFee             = ($this->transaction_type == 'start storing') ? $this->order->voucher : 0;
        $order                  = ($this->transaction_type == 'start storing') ? new OrderResource($this->order) : $this->order;
        $address_warehouse      = ($this->transaction_type == 'start storing') ? $this->order->area : null;
        $address_user           = ($this->transaction_type == 'start storing') ? $this->order->area : null;
        $pickup_order           = ($this->transaction_type == 'start storing') ? $this->order->pickup_order : null;
        $type_pickup            = ($this->transaction_type == 'start storing') ? $this->order->pickup_order->type_pickup : null;
        $data = [
            'id'                            => $this->id,
            'user_id'                       => $this->user_id,
            'transaction_type'              => $this->transaction_type,
            'order_id'                      => $this->order_id,
            'transaction'                   => $order,
            'status'                        => $this->status,
            'location_warehouse'            => $this->location_warehouse,
            'location_pickup'               => $this->location_pickup,
            'datetime_pickup'               => $this->datetime_pickup,
            'types_of_box_space_small_id'   => $this->types_of_box_space_small_id,
            'space_small_or_box_id'         => $this->space_small_or_box_id,
            'space_small_or_box'            => $this->boxOrSmallSpace,
            'amount'                        => $this->amount,
            'deliver_fee'                   => $this->order->deliver_fee,
            'box_small_space'               => $boxOrSmallSpaceFee,
            'pickup_order'                  => $pickup_order,
            'type_pickup'                   => $type_pickup,
            'created_at'                    => $this->created_at
        ];

        return $data;
    }
}
