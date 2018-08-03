<?php

namespace App\Entities;

use App\Core\Model\Searchable;
use Illuminate\Database\Eloquent\Model;

class TypePickup extends Model
{
    use Searchable;

    protected $table = 'types_of_pickup';

    protected $fillable = [
        'name'
    ];

    protected $searchable = ['id', 'name'];

    public function pickup_order()
    {
        return $this->hasMany('App\Entities\PickupOrder', 'type_pickup_id', 'id');
    }

    public function return_box()
    {
        return $this->hasMany('App\Entities\ReturnBox', 'type_pickup_id', 'id');
    }
}