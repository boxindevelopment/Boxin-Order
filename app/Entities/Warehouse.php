<?php

namespace App\Entities;

use App\Core\Model\Searchable;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use Searchable;

    protected $table = 'warehouses';

    protected $fillable = [
        'area_id', 'name', 'lat', 'long'
    ];

    protected $searchable = ['id', 'name'];

    public function area()
    {
        return $this->belongsTo('App\Entities\Area', 'area_id', 'id');
    }

    public function space()
    {
        return $this->hasMany('App\Entities\Space', 'warehouse_id', 'id');
    }
}