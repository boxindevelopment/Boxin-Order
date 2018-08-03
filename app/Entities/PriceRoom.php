<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class PriceRoom extends Model
{
    protected $table = 'price_room';

    protected $fillable = [
        'type_size_id', 'type_duration_id', 'price', 
    ];

    public function type_duration()
    {
        return $this->belongsTo('App\Entities\TypeDuration', 'type_duration_id', 'id');
    }

    public function type_size()
    {
        return $this->belongsTo('App\Entities\TypeSize', 'type_size_id', 'id');
    }

}