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
        $url = 'https://boxin-dev-webbackend.azurewebsites.net/';

        $data = [
            'id'                => $this->id,
            'name'              => $this->name,
            'size'              => $this->size,
            'image'             => is_null($this->image) ? null : $url.'images/types_of_size'.'/'.$this->image,
            'types_of_box_room' => new TypeBoxRoomResource($this->type_box_room),
        ];

        return $data;
    }
}
