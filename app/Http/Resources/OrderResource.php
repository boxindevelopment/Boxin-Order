<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'date' => $this->date->format('d-m-Y H:i:s'),
            'duration' => $this->duration,
            'total' => $this->total,
            'user' => !empty($this->user) ? $this->user->name : null,
            'area' => !empty($this->area) ? $this->area->name : null,
            'space' => !empty($this->space) ? $this->space->name : null,
            'box' => !empty($this->boxes) ? $this->boxes->name : null,
            'box_qty' => $this->box_qty
        ];
    }
}
