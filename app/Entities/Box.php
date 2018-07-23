<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    protected $table = 'boxes';

    protected $fillable = [
        'name', 'barcode', 'location', 'size', 'price'
    ];

    protected $hidden = ['created_at', 'updated_at'];
}