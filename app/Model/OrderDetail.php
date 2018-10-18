<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_details';

    protected $fillable = [
        'order_id', 'types_of_duration_id', 'room_or_box_id', 'types_of_box_room_id', 'types_of_size_id', 'name', 'duration', 'amount', 'start_date', 'end_date', 'status_id'
    ];

    public function order()
    {
        return $this->belongsTo('App\Model\Order', 'order_id', 'id');
    }

    public function box()
    {
        return $this->belongsTo('App\Model\Box', 'room_or_box_id', 'id');
    }

    public function room()
    {
        return $this->belongsTo('App\Model\Room', 'room_or_box_id', 'id');
    }

    public function type_box_room()
    {
        return $this->belongsTo('App\Model\TypeBoxRoom', 'types_of_box_room_id', 'id');
    }

    public function type_size()
    {
        return $this->belongsTo('App\Model\TypeSize', 'types_of_size_id', 'id');
    }

    public function type_duration()
    {
        return $this->belongsTo('App\Model\TypeDuration', 'types_of_duration_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo('App\Model\Status', 'status_id', 'id');
    }

    public function order_detail_box()
    {
        return $this->hasMany('App\Model\OrderDetailBox', 'order_detail_id', 'id');
    }

    public function toSearchableArray()
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

        if (!is_null($this->types_of_box_room_id)) {
            $type_box_room = [
                'id'        => $this->type_box_room->id,
                'name'      => $this->type_box_room->name,
            ];
        }

        if (!is_null($this->types_of_duration_id)) {
            $difference = $this->selisih;
            if($difference >= $this->total_time){
                $difference = $this->total_time;
            }else{
                $difference = $difference;
            }
            $duration = [
                'id'                => $this->type_duration->id,
                'count_time'        => $this->selisih,
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
            'types_of_box_room_id'  => $type_box_room,
            'types_of_size'         => $type_size,
            'order'     => $order,
            'location'  => $location,
            'duration'  => $duration,
        ];

        return $data;

    }
}
