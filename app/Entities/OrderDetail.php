<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_details';

    protected $fillable = [
        'order_id', 'room_or_box_id', 'type', 'name', 'type_duration_id', 'duration', 'amount', 'start_date', 'end_date', 'type_size_id'
    ];

    public function order()
    {
        return $this->belongsTo('App\Entities\Order', 'order_id', 'id');
    }

    public function box()
    {
        return $this->belongsTo('App\Entities\Box', 'room_or_box_id', 'id');
    }

    public function room()
    {
        return $this->belongsTo('App\Entities\Room', 'room_or_box_id', 'id');
    }

    public function type_size()
    {
        return $this->belongsTo('App\Entities\TypeSize', 'type_size_id', 'id');
    }

    public function type_duration()
    {
        return $this->belongsTo('App\Entities\TypeDuration', 'type_duration_id', 'id');
    }

    public function order_detail_box()
    {
        return $this->hasMany('App\Entities\OrderDetailBox', 'order_detail_id', 'id');
    }

    public function return_box()
    {
        return $this->hasMany('App\Entities\ReturnBox', 'order_detail_id', 'id');
    }

}