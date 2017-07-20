<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'tag';

    public function users()
    {
        return $this->belongsToMany('App\User', 'user_tag', 'tag_id', 'user_id');
    }
}
