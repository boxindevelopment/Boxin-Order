<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    
    public function toArray($request)
    {

        if (!is_null($this->order_id)) {
            $order = [
                'id'        => $this->order->id,
                'total'     => $this->order->total,
            ];
        }

        if (!is_null($this->types_of_size_id)) {
            $type_size = [
                'id'        => $this->type_size->id,
                'name'      => $this->type_size->name,
                'size'      => $this->type_size->size
            ];
        }

        if (!is_null($this->types_of_duration_id)) {
            $difference = $this->selisih;
            if($difference <= 0){
                $difference = 0;
            }
            if($difference >= $this->total_time){
                $difference = $this->total_time;
            }
            $duration = [
                'id'                => $this->type_duration->id,
                'count_time'        => $difference,
                'total_time'        => $this->total_time,
                'duration_storing'  => $this->duration,
                'name'              => $this->type_duration->name,
                'alias'             => $this->type_duration->alias,
            ];
        }

        $location = [
            'city_id'   => $this->order->area->city->id,
            'city'      => $this->order->area->city->name,
            'area_id'   => $this->order->area->id,
            'area'      => $this->order->area->name,
        ];

        $data = [
            'id'        => $this->id,
            'id_name'   => $this->id_name,
            'name'      => $this->name,
            'amount'    => $this->amount,
            'start_date'=> $this->start_date,
            'end_date'  => $this->end_date,
            'status'    => $this->status->name,
            'types_of_box_room_id'  => new TypeBoxRoomResource($this->type_box_room),
            'types_of_size'         => new TypeSizeResource($this->type_size),
            'order'     => $order,
            'location'  => $location,
            'duration'  => $duration,            
            'is_returned'   => $this->is_returned,
        ];

        return $data;
    }
}
