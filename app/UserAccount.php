<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * 用户账户
 */
class UserAccount extends Model
{
    protected $table = 'user_account';

    const ZS = 1;		//类型钻石
    const RECEIPT = 2;	//类型小票

    const BUSINESS_CHARGE = 1;    //充值
    const BUSINESS_EXCHANGE = 2;    //兑换
    const BUSINESS_FINDER_GET = 3;  //推荐奖励
    const BUSINESS_GIFT = 4;    //礼物
    const BUSINESS_CODE_HONGBAO = 5;    //口令红包

    /**
     * [in 入账]
     * @Author   Jason
     * @DateTime 2017-03-07T17:32:45+0800
     * @param    [type]                   $business     [业务]
     * @param    [type]                   $business_id [业务id]
     * @param    [type]                   $user_id      [用户id]
     * @param    [type]                   $type         [入账类型]
     * @param    [type]                   $value        [入账金额]
     * @param    [type]                   $remark       [入账说明]
     * @param    integer                  $status       [状态：0待确认，1已确认]
     * @return   [type]                                 [description]
     */
    public static function in( $business, $business_id, $user_id, $type, $value, $remark, $status = 1 ) 
    {
    	$ukey = $business . '_' . $business_id . '_' . $user_id . '_' . time();

    	$self               = new self;
		$self->user_id      = $user_id;
		$self->business 	= $business;
		$self->business_id = $business_id;
		$self->type         = $type;
		$self->value        = $value;
        $self->status       = $status;
        $self->ukey         = $ukey;
		$self->remark       = $remark;
    	$self->save();
    }

    /**
     * [out 出账]
     * @Author   Jason
     * @DateTime 2017-03-07T17:33:01+0800
     * @param    [type]                   $business     [业务]
     * @param    [type]                   $business_id [业务id]
     * @param    [type]                   $user_id      [用户id]
     * @param    [type]                   $type         [出账类型]
     * @param    [type]                   $value        [出账金额]
     * @param    [type]                   $remark       [出账说明]
     * @param    integer                  $status       [状态：0待确认，1已确认]
     * @return   [type]                                 [description]
     */
    public static function out( $business, $business_id, $user_id, $type, $value, $remark, $status = 1 )
    {
        $ukey = $business . '_' . $business_id . '_' . $user_id . '_' . time();

        $self               = new self;
        $self->user_id      = $user_id;
        $self->business     = $business;
        $self->business_id = $business_id;
        $self->type         = $type;
        $self->value        = -abs($value);
        $self->status       = $status;
        $self->ukey         = $ukey;
        $self->remark       = $remark;
        $self->save();

        $total = self::total($user_id, $type );
        if ( $total < 0 ) {
            throw new \Exception("value no enough");
        }
    }

    /**
     * [total 用户的余额]
     * @Author   Jason
     * @DateTime 2017-03-07T17:41:10+0800
     * @param    [type]                   $type [description]
     * @return   [type]                         [description]
     */
    public static function total( $user_id, $type )
    {
        return self::where('user_id', $user_id)->where('type', $type)->sum('value');
    }
}
