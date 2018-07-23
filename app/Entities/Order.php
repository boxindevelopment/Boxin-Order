<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'user_id', 'area_id', 'space_id', 'date', 'duration', 'box',
        'box_qty', 'total', 'name'
    ];

    protected $casts = [
        'date' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo('App\Entities\User', 'user_id', 'id');
    }

    public function area()
    {
        return $this->belongsTo('App\Entities\Area', 'area_id', 'id');
    }

    public function space()
    {
        return $this->belongsTo('App\Entities\Space', 'space_id', 'id');
    }

    public function boxes()
    {
        return $this->belongsTo('App\Entities\Box', 'box', 'id');
    }

    public function details()
    {
        return $this->hasMany('App\Entities\OrderDetail', 'order_id', 'id');
    }
}