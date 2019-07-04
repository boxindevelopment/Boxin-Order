<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ReturnBoxPayment extends Model
{

    protected $table = 'return_box_payments';

    protected $fillable = [
        'order_detail_id', 
        'user_id', 
        'payment_type', 
        'bank', 
        'amount', 
        'image_transfer', 
        'status_id', 
        'id_name',
        'midtrans_url',
        'midtrans_start_transaction',
        'midtrans_expired_transaction'
    ];

    public function order_detail()
    {
        return $this->belongsTo('App\Model\OrderDetail', 'order_detail_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\Model\User', 'user_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo('App\Model\Status', 'status_id', 'id');
    }

}