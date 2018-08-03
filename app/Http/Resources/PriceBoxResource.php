<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PriceBoxResource extends JsonResource
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

        if (!is_null($this->type_duration_id)) {
            $type_duration = [
                'id'                  => $this->type_duration->id,
                'name'                => $this->type_duration->name,
            ];
        } 

        $data = [
            'id'                => $this->id,
            'price'             => $this->price,
            'type_size'         => $type_size,
            'type_duration'     => $type_duration,
        ];

        return $data;
    }
}
