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
        $order                  = $this->order;
        if($this->transaction_type == 'start storing'){
            $order              = new OrderResource($this->order);
        } else if ($this->transaction_type == 'take'){
            $order              = new OrderTakeResource($this->order);
        } else if ($this->transaction_type == 'back warehouse'){
            $order              = new OrderBackWarehouseResource($this->order);
        } else if ($this->transaction_type == 'extend'){
            $order              = new ExtendOrderDetailResource($this->order);
        } else if ($this->transaction_type == 'terminate'){
            $order              = new ReturnBoxesResource($this->order);
        }

        $address_warehouse      = ($this->transaction_type == 'start storing') ? $this->order->area : null;
        $address_user           = ($this->transaction_type == 'start storing') ? $this->order->area : null;
        $pickup_order           = ($this->transaction_type == 'start storing') ? $this->order->pickup_order : null;
        $type_pickup            = ($this->transaction_type == 'start storing') ? $this->order->pickup_order->type_pickup : null;
        $data = [
            'id'                            => $this->id,
            'user_id'                       => $this->user_id,
            'order_id'                      => $this->order_id,
            'transaction_type'              => $this->transaction_type,
            'storage_fee'                   => $this->amount-($voucherFee+$this->order->deliver_fee),
            'amount'                        => $this->amount,
            'deliver_fee'                   => $this->order->deliver_fee,
            'status'                        => $order->status->name,
            'box_id'                        => $boxOrSmallSpaceFee,
            'location_warehouse'            => $this->location_warehouse,
            'location_pickup'               => $this->location_pickup,
            'datetime_pickup'               => $this->datetime_pickup,
            'discount'                      => $voucherFee,
            'created_at'                    => $this->created_at
        ];

        return $data;
    }
}
