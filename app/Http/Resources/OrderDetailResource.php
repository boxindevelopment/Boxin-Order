<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
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
            $difference = $this->selisih + 1;
            if($difference >= $this->total_time){
                $difference = $this->total_time;
            }else{
                $difference = $difference;
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
            'city_id'   => $this->order->space->warehouse->area->city->id,
            'city'      => $this->order->space->warehouse->area->city->name,
            'area_id'   => $this->order->space->warehouse->area->id,
            'area'      => $this->order->space->warehouse->area->name,
            'warehouse_id' => $this->order->space->warehouse->id,
            'warehouse' => $this->order->space->warehouse->name,
            'space_id'  => $this->order->space->id,
            'space'     => $this->order->space->name,
        ];

        $data = [
            'id'        => $this->id,
            'name'      => $this->name,
            'amount'    => $this->amount,
            'start_date'=> $this->start_date,
            'end_date'  => $this->end_date,
            'status'    => $this->status->name,
            'types_of_box_room_id'  => new TypeBoxRoomResource($this->type_size),
            'types_of_size'         => new TypeSizeResource($this->type_size),
            'order'     => $order,
            'location'  => $location,
            'duration'  => $duration,
        ];

        return $data;
    }
}
