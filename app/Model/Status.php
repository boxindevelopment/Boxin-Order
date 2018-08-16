<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{

    protected $table = 'status';

    const ON_THE_WAY_TO_YOU = 2;
    const UPCOMMING         = 3;
    const STORED            = 4;
    const SUCCESS           = 5;
    // const FAILED            = 6;
    // const FAILED            = 6;
    // const FAILED            = 6;
    // const FAILED            = 6;
    // const FAILED            = 6;
    // const FAILED            = 6;

    protected $fillable = [
        'name'
    ];

    protected $searchable = ['id', 'name'];

    public function order()
    {
        return $this->hasMany('App\Model\Order', 'status_id', 'id');
    }

    public function pickup_order()
    {
        return $this->hasMany('App\Model\PickupOrder', 'status_id', 'id');
    }

    public function detail_order()
    {
        return $this->hasMany('App\Model\DetailOrder', 'status_id', 'id');
    }

    public function payment()
    {
        return $this->hasMany('App\Model\Payment', 'status_id', 'id');
    }

}
