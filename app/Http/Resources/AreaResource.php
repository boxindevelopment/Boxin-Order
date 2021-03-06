<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class AreaResource extends Resource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'city'          => new CityResource($this->city),  
        ];
    }
}
