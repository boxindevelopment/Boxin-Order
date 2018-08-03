<?php

namespace App\Entities;

use App\Core\Model\Searchable;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use Searchable;

    protected $table = 'areas';

    protected $fillable = [
        'city_id', 'name'
    ];

    protected $searchable = ['id', 'name'];

    public function city()
    {
        return $this->belongsTo('App\Entities\City', 'city_id', 'id');
    }

    public function warehouse()
    {
        return $this->hasMany('App\Entities\Warehouse', 'area_id', 'id');
    }
}