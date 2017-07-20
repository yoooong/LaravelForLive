<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WechatMenu extends Model
{
    protected $table = 'wechat_menu';

    public function parent()
    {
        return $this->belongsTo('App\WechatMenu');
    }

    public function children()
    {
        return $this->hasMany('App\WechatMenu', 'parent_id', 'id');
    }
}
