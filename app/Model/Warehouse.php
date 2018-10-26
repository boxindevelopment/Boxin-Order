<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{

    protected $table = 'warehouses';

    protected $fillable = [
        'area_id', 'name', 'lat', 'long', 'id_name'
    ];

    protected $searchable = ['id', 'name'];

    public function area()
    {
        return $this->belongsTo('App\Model\Area', 'area_id', 'id');
    }

    public function space()
    {
        return $this->hasMany('App\Model\Space', 'warehouse_id', 'id');
    }
}