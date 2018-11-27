<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SpaceResource extends JsonResource
{
    public function toArray($request)
    {

        $data = [
            'id'                => $this->id,
            'name'              => $this->name,
        ];

        return $data;
    }
}
