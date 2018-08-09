<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BoxResource extends JsonResource
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
            'id'                => $this->id,
            'available'         => $this->available,
            'types_of_size'     => new TypeSizeResource($this->type_size),
        ];

        return $data;
    }
}
