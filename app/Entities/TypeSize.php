<?php

namespace App\Entities;

use App\Core\Model\Searchable;
use Illuminate\Database\Eloquent\Model;

class TypeSize extends Model
{
    use Searchable;

    protected $table = 'types_of_size';

    protected $fillable = [
        'name'
    ];

    protected $searchable = ['id', 'name'];

    public function order_detail()
    {
        return $this->hasMany('App\Entities\OrderDetail', 'type_size_id', 'id');
    }

    public function room()
    {
        return $this->hasMany('App\Entities\Room', 'type_size_id', 'id');
    }

    public function price_room()
    {
        return $this->hasMany('App\Entities\PriceRoom', 'type_size_id', 'id');
    }

    public function box()
    {
        return $this->hasMany('App\Entities\Box', 'type_size_id', 'id');
    }

    public function price_box()
    {
        return $this->hasMany('App\Entities\PriceBox', 'type_size_id', 'id');
    }

}