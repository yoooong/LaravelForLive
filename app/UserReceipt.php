<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserReceipt extends Model
{
	protected $primaryKey = 'user_id';
    protected $table = 'user_receipt';
}
