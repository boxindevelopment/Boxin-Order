<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (!is_null($this->type_size_id)) {
            $type_size = [
                'id'                  => $this->type_size->id,
                'name'                => $this->type_size->name,
            ];
        } 

        $data = [
            'id'                => $this->id,
            'name'              => $this->name,
            'size'              => $this->size,
            'available'         => $this->available,
            'type_size'         => $type_size,
        ];

        return $data;
    }
}
