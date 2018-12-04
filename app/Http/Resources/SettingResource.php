<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class SettingResource extends Resource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'value'         => $this->value,
            'unit'          => $this->unit,
            'description'   => $this->description,
        ];
    }
}
