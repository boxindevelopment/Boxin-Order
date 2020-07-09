<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Shelves extends Model
{

    protected $table = 'shelves';

    protected $fillable = [
        'area_id', 'name', 'id_name'
    ];

    public function area()
    {
        return $this->belongsTo('App\Model\Area', 'area_id', 'id');
    }

    public function box()
    {
        return $this->hasMany('App\Model\Box', 'shelves_id', 'id');
    }

    public function space_small()
    {
        return $this->hasMany('App\Model\SpaceSmall', 'area_id', 'id');
    }

    public function order_detail()
    {
        return $this->hasMany('App\Model\OrderDetail', 'room_or_box_id', 'id');
    }

}
