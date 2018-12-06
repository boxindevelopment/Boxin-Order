<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ReturnBoxPayment extends Model
{

    protected $table = 'return_box_payments';

    protected $fillable = [
        'return_boxes_id', 'user_id', 'payment_type', 'bank', 'amount', 'image_transfer', 'status_id', 'id_name'
    ];

    public function return_boxes()
    {
        return $this->belongsTo('App\Model\ReturnBoxes', 'return_boxes_id', 'id');
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