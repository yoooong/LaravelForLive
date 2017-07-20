<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;

class Coin extends Model 
{
    protected $table = 'coin_bill';

    //查询用户余额
    public static function get( $user_id )
    {
        if ( $user_id ) {
            //获取消耗
            $xh = self::where('user_id', $user_id)
                      ->where('business_type', '<', 0)
                      ->where('state', '>', 0)
                      ->sum('coin');

            //获取增值
            $zz = self::where('user_id', $user_id)
                      -> where('business_type', '>', 0)
                      -> where('state', '>', 0)
                      ->sum('coin');

            $ls = $zz - $xh;
            return sprintf("%.2f", $ls);
        } else {
            return 0;
        }
    }

    //添加账单项目  -1 兑换小票  -2 送礼物  -3 买vip  1充值  2 收礼物 state状态 1 正常  2锁定  -1未生效（未支付）-2作废 
    public static function add($coin, $business_type, $content = '', $user_id = 0, $business_id = 0)
    {
        $q = self::where('user_id', $user_id)
                ->where('business_type', $business_type)
                ->where('business_id', $business_id)
                ->count();

        // if ($user_id && $coin > 0 && $q == 0) {
        if ($user_id && $q == 0) {
            $data                = new self();
            $data->user_id       = $user_id;
            $data->coin          = $coin;
            $data->business_type = $business_type;
            $data->business_id   = $business_id;
            $data->state         = 1;
            $data->content       = $content;
            $ret = $data->save();

            return $data->id;
        } else {
            return 0;
        }
    }
}
