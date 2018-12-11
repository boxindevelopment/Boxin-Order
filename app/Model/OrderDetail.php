<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_details';

    protected $fillable = [
        'order_id', 'types_of_duration_id', 'room_or_box_id', 'types_of_box_room_id', 'types_of_size_id', 'name', 'duration', 'amount', 'start_date', 'end_date', 'status_id', 'is_returned', 'id_name'
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

    public function return_box()
    {
        return $this->hasMany('App\Model\ReturnBoxes', 'order_detail_id', 'id');
    }
    
    public function change_box_payment()
    {
        return $this->hasMany('App\Model\ChangeBoxPayment', 'order_detail_id', 'id');
    }

    public function change_box()
    {
        return $this->hasMany('App\Model\ChangeBox', 'order_detail_id', 'id');
    }
    
    public function toSearchableArray()
    {
        $url = 'https://boxin-dev-webbackend.azurewebsites.net/';
        
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
                'size'      => $this->type_size->size,
                'image'     => is_null($this->type_size->image) ? null : $url.'images/types_of_size'.'/'.$this->type_size->image,
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
        
        $pick_up = null;
        if(!is_null($this->order->pickup_order)){
            
            if($this->order->pickup_order->type_pickup->id == 1){
                $pick_up = [
                    'pickup_id'         => $this->order->pickup_order->id,
                    'address'           => $this->order->pickup_order->address,
                    'date'              => $this->order->pickup_order->date,
                    'note'              => $this->order->pickup_order->note,
                    'pickup_fee'        => intval($this->order->pickup_order->pickup_fee),
                    'driver_name'       => $this->order->pickup_order->driver_name,
                    'driver_phone'      => $this->order->pickup_order->driver_phone,              
                    'time_pickup'       => $this->order->pickup_order->time_pickup,
                    'type_pickup_id'    => $this->order->pickup_order->type_pickup->id,
                    'type_pickup_name'  => $this->order->pickup_order->type_pickup->name,
                ];
            } 
            
        }

        $payment = null;
        if(!is_null($this->order->payment)){
            
            $payment = [
                'id'         => $this->order->payment->id,
                'order_id'   => $this->order->payment->order_id,
                'status_id'  => $this->order->payment->status->id,                
                'status'     => $this->order->payment->status->name,
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
            'code'      => $this->id_name,
            'name'      => $this->name,
            'amount'    => $this->amount,
            'start_date'=> $this->start_date,
            'end_date'  => $this->end_date,
            'status_id' => $this->status->id,            
            'status'    => $this->status->name,
            'types_of_box_room_id'  => $type_box_room,
            'types_of_size'         => $type_size,
            'order'     => $order,
            'location'  => $location,
            'duration'  => $duration,
            'pickup'    => $pick_up,
            'payment'   => $payment,
        ];

        return $data;

    }
}
