<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SpaceResource extends JsonResource
{
    public function toArray($request)
    {

        $data = [
            'available'     => $this->available,
            'area'     		=> new AreaResource($this->area),            
            'types_of_size' => new TypeSizeResource($this->type_size), 
        ];

        return $data;
    }
}
