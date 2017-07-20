<?php
namespace App\Http\Controllers\Api;

use App\Gift;
use App\Http\Controllers\Controller;
use App\User;
use App\UserAccount;
use App\UserGift;
use App\UserReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class GiftController extends Controller
{
    // const ZS_RATE = 1; //钻石汇率

    protected $zs_rate;

    public function __construct()
    {
        $this->zs_rate = env('ZS_RECEIPT_RATE');
    }

    //礼物列表
    public function index(Request $request)
    {
        $gifts = Gift::where('state', 1)->get();

        return response()->json(['code' => 1000, 'data' => $gifts]);
    }

    //送礼物
    public function send(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $uid = $user->id;
        $to_user_id = $request->input('to_user_id');
        $gift_id = $request->input('gift_id');

        $to_user = User::where('uuid', $to_user_id)->first();

        $gift = Gift::where('id', $gift_id)->first();
        $price = $gift->price;

        $zs = $user_zs = UserAccount::total( $user->id, UserAccount::ZS );

        if ($zs < $price) {
            return response()->json(['code' => 2001, 'msg' => '余额不足，请充值']);
        }

        DB::beginTransaction();

        try {
            $receipt = $price * $this->zs_rate;

            $userGift = new UserGift();
            $userGift->user_id = $to_user->id;
            $userGift->gift_id = $gift_id;;
            $userGift->receipt = $receipt;;
            $userGift->state = 1;
            $userGift->from_uid = $uid;
            $ret = $userGift->save();;
            if (!$ret) {
                DB::rollback();
                return response()->json(['code' => 2002, 'msg' => '送礼失败，请重试']);
            }

            UserAccount::out( UserAccount::BUSINESS_GIFT, $userGift->id, $user->id, UserAccount::ZS, $price, '钻石购买礼物' );
            //接收用户直接接收到小票
            UserAccount::in( UserAccount::BUSINESS_GIFT, $userGift->id, $to_user->id, UserAccount::RECEIPT, $receipt, '收到礼物转成的小票' );

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['code' => 2003, 'msg' => '系统繁忙，请频繁操作']);
        }

        return response()->json(['code' => 1000, 'data' => ['id' => $userGift->id]]);
    }

}
     