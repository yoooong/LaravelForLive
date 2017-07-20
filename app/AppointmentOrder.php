<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AppointmentOrder extends Model
{
    protected $table = 'appointment_order';

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function target_user()
    {
    	return $this->belongsTo('App\User');
    }
}
