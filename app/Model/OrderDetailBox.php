<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderDetailBox extends Model
{
    protected $table = 'order_detail_boxes';

    protected $fillable = [
        'order_detail_id', 'category_id', 'item_name', 'item_image', 'note', 'status_id'
    ];

    public function order_detail()
    {
        return $this->belongsTo('App\Model\OrderDetail', 'order_detail_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo('App\Model\Category', 'category_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo('App\Model\Status', 'status_id', 'id');
    }

    public function change_box()
    {
        return $this->hasMany('App\Model\ChangeBox', 'order_detail_box_id', 'id');
    }

    public function getUrlAttribute()
    {
        if (!empty($this->item_image)) {
            return asset(config('image.url.detail_item_box') . $this->item_image);
        }

        return null;
    }
}