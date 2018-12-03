<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BoxResource extends JsonResource
{
    
    public function toArray($request)
    {

        $url = 'https://boxin-dev-webbackend.azurewebsites.net/';

        $data = [
            'available'     => $this->available,
            'id'            => $this->type_size->id,
            'name'          => $this->type_size->name,
            'size'          => $this->type_size->size,
            'image'         => is_null($this->type_size->image) ? null : $url.'images/types_of_size'.'/'.$this->type_size->image,
            'types_of_box_room' => new TypeBoxRoomResource($this->type_size->type_box_room),
            'area'          => new AreaResource($this->shelves->space->area),  
        ];

        return $data;
    }
}
