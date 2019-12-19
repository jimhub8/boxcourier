<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class PasswordSecurity extends Model
{
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
