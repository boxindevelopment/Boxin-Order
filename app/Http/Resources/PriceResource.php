<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
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
            'id'                    => $this->id,
            'price'                 => $this->price,
            'types_of_size'         => new TypeSizeResource($this->type_size),
            'type_duration'         => new TypeDurationResource($this->type_duration),
        ];

        return $data;
    }
}
