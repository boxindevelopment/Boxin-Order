<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_details';

    protected $fillable = [
        'order_id', 'item_name', 'item_image'
    ];

    public function order()
    {
        return $this->belongsTo('App\Entities\Order', 'order_id', 'id');
    }
}