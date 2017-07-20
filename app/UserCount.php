<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserCount extends Model
{
	protected $primaryKey = 'user_id';
    protected $table = 'user_count';
}
