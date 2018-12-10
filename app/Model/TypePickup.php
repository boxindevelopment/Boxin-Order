<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TypePickup extends Model
{
    protected $table = 'types_of_pickup';

    protected $fillable = [
        'name'
    ];

    protected $searchable = ['id', 'name'];

    public function pickup_order()
    {
        return $this->hasMany('App\Model\PickupOrder', 'types_of_pickup_id', 'id');
    }

    public function return_box()
    {
        return $this->hasMany('App\Model\ReturnBoxes', 'types_of_pickup_id', 'id');
    }

    public function change_box()
    {
        return $this->hasMany('App\Model\ChangeBoxes', 'types_of_pickup_id', 'id');
    }

}