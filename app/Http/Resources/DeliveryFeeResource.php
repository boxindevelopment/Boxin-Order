<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryFeeResource extends JsonResource
{
    public function toArray($request)
    {

        $data = [
            'id'                => $this->id,
            'fee'               => $this->fee,
            'area'              => new AreaResource($this->area),
        ];

        return $data;
    }
}
