<?php

namespace App\Model;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, SoftDeletes;

    protected $fillable = [
        'first_name', 'last_name', 'phone', 'email', 'password', 'roles_id', 'status'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function roles()
    {
        return $this->belongsTo('App\Model\Roles', 'roles_id', 'id');
    }

    public function order()
    {
        return $this->hasMany('App\Model\Order', 'user_id', 'id');
    }

    public function payment()
    {
        return $this->hasMany('App\Model\Payment', 'user_id', 'id');
    }

    public function return_box_payment()
    {
        return $this->hasMany('App\Model\ReturnBoxPayment', 'user_id', 'id');
    }

    public function admin()
    {
        return $this->hasMany('App\Model\Admin', 'user_id', 'id');
    }
    
}
