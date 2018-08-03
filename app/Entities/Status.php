<?php

namespace App\Entities;

use App\Core\Model\Searchable;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use Searchable;

    protected $table = 'status';

    protected $fillable = [
        'name'
    ];

    protected $searchable = ['id', 'name'];

    public function order()
    {
        return $this->hasMany('App\Entities\Order', 'status_id', 'id');
    }

    public function pickup_order()
    {
        return $this->hasMany('App\Entities\PickupOrder', 'status_id', 'id');
    }
    
    public function return_box()
    {
        return $this->hasMany('App\Entities\ReturnBox', 'status_id', 'id');
    }
}