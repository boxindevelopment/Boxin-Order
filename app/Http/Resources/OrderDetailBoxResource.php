<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailBoxResource extends JsonResource
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
            'order_detail_id'   => $this->order_detail_id,
            'name'              => $this->item_name,
            'image'             => is_null($this->item_image) ? null : (asset('images/detail_item_box').'/'.$this->item_image),
            'note'              => $this->note,
            'created_date'      => $this->created_at->format('Y-m-d'),
        ];

        return $data;
    }
}
