<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserTag extends Model
{
    protected $table = 'user_tag';

    public function tag()
    {
        return $this->belongsTo('App\Tag');
    }
}
