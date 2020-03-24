<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VoucherResource extends JsonResource
{
    public function toArray($request)
    {

        $data = [
            'id'                => $this->id,
            'name'              => $this->name,
            'code'              => $this->code,
            'type_voucher'      => $this->type_voucher == 1 ? 'percen' : 'nominal',
            'value'             => $this->value,
            'min_amount'        => $this->min_amount,
            'max_value'         => $this->max_value,
            'start_date'        => $this->start_date->format('Y-m-d'),
            'end_date'          => $this->end_date->format('Y-m-d'),
            'description'       => $this->description,
            'term_condition'    => $this->term_condition,
            'image'             => is_null($this->image) ? null : env('APP_ADMIN') . 'images/voucher'.'/'.$this->image,
            'status'            => new StatusResource($this->status),
        ];

        return $data;
    }
}
