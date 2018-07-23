<?php

namespace App\Entities;

use App\Core\Model\Searchable;
use Illuminate\Database\Eloquent\Model;

class Space extends Model
{
    use Searchable;

    protected $table = 'spaces';

    protected $fillable = [
        'warehouse_id', 'name'
    ];

    protected $searchable = ['id', 'name'];

    public function warehouse()
    {
        return $this->belongsTo('App\Entities\Warehouse', 'warehouse_id', 'id');
    }

    public function rooms()
    {
        return $this->hasMany('App\Entities\Room', 'space_id', 'id');
    }
}