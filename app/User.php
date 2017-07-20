<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nickname', 'age', 'sex', 'sexual', 'constell', 'marital', 'country', 'province', 'city', 'signature',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function count()
    {
        return $this->hasOne('App\UserCount');
    }

    public function receipt()
    {
        return $this->hasOne('App\UserReceipt');
    }

    public function fans()
    {
        return $this->belongsToMany('App\User', 'user_fan', 'user_id',  'fan_user_id');
    }

    public function idols()
    {
        return $this->belongsToMany('App\User', 'user_fan', 'fan_user_id',  'user_id');
    }

    public function blacklist()
    {
        return $this->belongsToMany('App\User', 'blacklist', 'user_id', 'to_user_id');
    }

    public function tags()
    {
        return $this->belongsToMany('App\Tag', 'user_tag', 'user_id', 'tag_id');
    }
}
