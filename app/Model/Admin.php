<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{

    protected $table = 'admins';

    protected $fillable = [
        'user_id', 'area_id'
    ];

    public function area()
    {
        return $this->belongsTo('App\Model\Area', 'area_id', 'id');
    }

}