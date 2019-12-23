<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class PasswordSecurity extends Model
{
    public $fillable = ['user_id', 'google2fa_enable', 'google2fa_secret'];
    protected $guarded = [];


    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
