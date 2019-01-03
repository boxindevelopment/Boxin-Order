<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VoucherResource extends JsonResource
{
    public function toArray($request)
    {
        $url = (env('DB_DATABASE') == 'coredatabase') ? 'https://boxin-dev-webbackend.azurewebsites.net/' : 'https://boxin-prod-webbackend.azurewebsites.net/';

        $data = [
            'id'                => $this->id,
            'name'              => $this->name,
            'code'              => $this->code,
            'type_voucher'      => $this->type_voucher == 1 ? 'percen' : 'nominal',
            'value'             => intval($this->value),
            'start_date'        => $this->start_date->format('Y-m-d'),
            'end_date'          => $this->end_date->format('Y-m-d'),
            'description'       => $this->description,
            'image'             => is_null($this->image) ? null : $url.'images/voucher'.'/'.$this->image,
            'status'            => new StatusResource($this->status),
        ];

        return $data;
    }
}
