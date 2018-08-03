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
        if (!is_null($this->user_id)) {
            $user = [
                'id'        => $this->order->user->id,
                'first_name'=> $this->order->user->first_name,
                'last_name' => $this->order->user->last_name,
                'email'     => $this->order->user->email,
                'phone'     => $this->order->user->phone,
            ];
        }

        if (!is_null($this->order_id)) {
            $order = [
                'id'        => $this->order->id,
                'total'     => $this->order->total,
            ];
        }

        if (!is_null($this->type_size_id)) {
            $type_size = [
                'id'        => $this->type_size->id,
                'name'      => $this->type_size->name,
            ];
        }

        if (!is_null($this->type_duration_id)) {
            $difference = $this->selisih + 1;
            if($difference >= $this->total_time){
                $difference = $total_time;
            }else{
                $difference = $difference;
            }
            $duration = [
                'id'                => $this->type_duration->id,
                'count_time'        => $difference,
                'total_time'        => $this->total_time,
                'duration_storing'  => $this->duration,
                'name'              => $this->type_duration->name,
            ];
        }

        $location = [
            'city'      => $this->order->space->warehouse->area->city->name,
            'areas'     => $this->order->space->warehouse->area->name,
            'warehouse' => $this->order->space->warehouse->name,
            'space'     => $this->order->space->name,
        ];

        $data = [
            'id'        => $this->id,
            'name'      => $this->name,
            'type'      => $this->type,
            'order_date'=> $this->start_date,
            'status'    => $this->order->status->name,
            'type_size' => $type_size,
            'user'      => $user,
            'order'     => $order,
            'location'  => $location,
            'duration'  => $duration,
        ];

        return $data;
    }
}
