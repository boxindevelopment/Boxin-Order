<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
          'id'              => $this->id,
          'add_item_box_id' => $this->add_item_box_id,
          'category'        => new CategoryResource($this->category),
          'name'            => $this->item_name,
          'image'           => $this->image,
          'note'            => $this->note,
          'created_date'    => $this->created_at->format('d-m-Y'),
          'status'          => new StatusResource($this->status)
        ];
    }
}
