<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'rooms';

    protected $fillable = [
        'space_id', 'name', 'type_size_id', 'size'
    ];

    public function space()
    {
        return $this->belongsTo('App\Entities\Space', 'space_id', 'id');
    }

    public function type_size()
    {
        return $this->belongsTo('App\Entities\TypeSize', 'type_size_id', 'id');
    }
    
    public function order_detail()
    {
        return $this->hasMany('App\Entities\OrderDetail', 'room_or_box_id', 'id');
    }

}