<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AppointmnetUserScore extends Model
{
    protected $table = 'appointment_user_score';

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
