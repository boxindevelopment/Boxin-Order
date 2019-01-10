<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{

    protected $table = 'status';

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

    public function return_box_payment()
    {
        return $this->hasMany('App\Model\ReturnBoxPayment', 'status_id', 'id');
    }

    public function space()
    {
        return $this->hasMany('App\Model\Space', 'status_id', 'id');
    }

    public function box()
    {
        return $this->hasMany('App\Model\Box', 'status_id', 'id');
    }

    public function change_box()
    {
        return $this->hasMany('App\Model\ChangeBox', 'status_id', 'id');
    }
    
    public function order_detail_box()
    {
        return $this->hasMany('App\Model\OrderDetailBox', 'status_id', 'id');
    }

    public function change_box_payment()
    {
        return $this->hasMany('App\Model\ChangeBoxPayment', 'status_id', 'id');
    }

    public function voucher()
    {
        return $this->hasMany('App\Model\Voucher', 'status_id', 'id');
    }

    public function banner()
    {
        return $this->hasMany('App\Model\Banner', 'status_id', 'id');
    }
}
