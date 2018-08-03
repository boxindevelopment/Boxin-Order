<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    protected $table = 'boxes';

    protected $fillable = [
        'space_id', 'type_size_id', 'name', 'barcode', 'location', 'size', 'price'
    ];

    protected $hidden = ['created_at', 'updated_at'];

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