<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'user_id', 'space_id', 'total', 'qty' 
    ];

    // protected $casts = [
    //     'date' => 'datetime'
    // ];

    public function user()
    {
        return $this->belongsTo('App\Entities\User', 'user_id', 'id');
    }

    public function space()
    {
        return $this->belongsTo('App\Entities\Space', 'space_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo('App\Entities\Status', 'status_id', 'id');
    }

    public function order_detail()
    {
        return $this->hasMany('App\Entities\OrderDetail', 'order_id', 'id');
    }

    public function pickup_order()
    {
        return $this->hasMany('App\Entities\PickupOrder', 'order_id', 'id');
    }

}