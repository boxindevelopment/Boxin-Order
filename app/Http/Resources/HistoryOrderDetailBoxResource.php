<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HistoryOrderDetailBoxResource extends JsonResource
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
          'id'           => $this->id,
          'item_name'    => $this->item_name,
          'item_image'   => $this->image,
          'note'         => $this->note,
          'action'       => $this->action,
          'created_at'   => $this->created_at,
          'category'     => $this->category,
          'order_detail' => $this->order_detail
        ];
    }
}
