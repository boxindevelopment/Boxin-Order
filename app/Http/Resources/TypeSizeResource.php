<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TypeSizeResource extends JsonResource
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
            'name'              => $this->name,
            'size'              => $this->size,
            'types_of_box_room' => new TypeBoxRoomResource($this->type_box_room),
        ];

        return $data;
    }
}
