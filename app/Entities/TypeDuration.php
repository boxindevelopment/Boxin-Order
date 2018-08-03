<?php

namespace App\Entities;

use App\Core\Model\Searchable;
use Illuminate\Database\Eloquent\Model;

class TypeDuration extends Model
{
    use Searchable;

    protected $table = 'types_of_duration';

    protected $fillable = [
        'name'
    ];

    protected $searchable = ['id', 'name'];

    public function order_detail()
    {
        return $this->hasMany('App\Entities\OrderDetail', 'type_duration_id', 'id');
    }
}