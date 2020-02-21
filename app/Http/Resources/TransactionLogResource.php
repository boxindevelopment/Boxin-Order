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
        $code                   = '';
        $order_detail_id        = null;
        if($this->transaction_type == 'start storing'){
            $order              = new OrderResource($this->order);
            $code               = $order->order_detail[0]->id_name;
            $order_detail_id    = $order->order_detail[0]->id;
        } else if ($this->transaction_type == 'take'){
            $order              = new OrderTakeResource($this->order);
            $code               = $order->order_detail->id_name;
            $order_detail_id    = $order->order_detail->id;
        } else if ($this->transaction_type == 'back warehouse'){
            $order              = new OrderBackWarehouseResource($this->order);
            $code               = $order->order_detail[0]->id_name;
            $order_detail_id    = $order->order_detail[0]->id;
        } else if ($this->transaction_type == 'extend'){
            $order              = new ExtendOrderDetailResource($this->order);
            $code               = $order->order_detail->id_name;
            $order_detail_id    = $order->order_detail->id;
        } else if ($this->transaction_type == 'terminate'){
            $order              = new ReturnBoxesResource($this->order);
            $code               = $order->order_detail[0]->id_name;
            $order_detail_id    = $order->order_detail[0]->id;
        }

        $address_warehouse      = ($this->transaction_type == 'start storing') ? $this->order->area : null;
        $address_user           = ($this->transaction_type == 'start storing') ? $this->order->area : null;
        $pickup_order           = ($this->transaction_type == 'start storing') ? $this->order->pickup_order : null;
        $type_pickup            = ($this->transaction_type == 'start storing') ? $this->order->pickup_order->type_pickup : null;
        $data = [
            'id'                            => $this->id,
            'user_id'                       => $this->user_id,
            'order_id'                      => $this->order_id,
            'code'                          => $code,
            'transaction_type'              => $this->transaction_type,
            'storage_fee'                   => $this->amount-($voucherFee+$this->order->deliver_fee),
            'amount'                        => $this->amount,
            'deliver_fee'                   => $this->order->deliver_fee,
            'status'                        => $order->status->name,
            'box_id'                        => $this->boxOrSmallSpace,
            'order_detail_id'               => $order_detail_id,
            'location_warehouse'            => $this->location_warehouse,
            'location_pickup'               => $this->location_pickup,
            'datetime_pickup'               => $this->datetime_pickup,
            'discount'                      => $voucherFee,
            'created_at'                    => $this->created_at
        ];

        return $data;
    }
}
