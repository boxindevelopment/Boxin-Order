<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class CityResource extends Resource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name, 
        ];
    }
}
