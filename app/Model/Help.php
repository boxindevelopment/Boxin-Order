<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Help extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'subject',
        'message'
    ];

}
