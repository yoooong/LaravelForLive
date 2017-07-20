<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\UserAccount;
use App\UserExchange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class ExchangeController extends Controller 
{
	protected $receipt_rmb_rate;

    public function __construct()
    {
        $this->receipt_rmb_rate = env( 'RECEIPT_TO_RMB_RATE' );
    }

	public function query()
	{
		$user = JWTAuth::parseToken()->authenticate();
		$receipt = UserAccount::total($user->id, UserAccount::RECEIPT );

		$rmb = UserExchange::where('user_id', $user->id)->where('state', 0)->sum('rmb');

		$data['receipt'] = $receipt;
		$data['rmb']	 = $rmb;
		return response()->json(['code' => 1000, 'data' => $data]);
	}

	public function queryGetRmb()
	{
		$user = JWTAuth::parseToken()->authenticate();
		$rmb = UserExchange::where('user_id', $user->id)->where('state', 1)->sum('rmb');

		$data['rmb']	 = $rmb;
		return response()->json(['code' => 1000, 'data' => $data]);
	}

	//兑换人民币
    public function rmb( Request $request )
    {
        $user = JWTAuth::parseToken()->authenticate();

        // $user_receipt = $user->receipt;
        $user_receipt = UserAccount::total( $user->id, UserAccount::RECEIPT );

        if ( $user_receipt <= 0 ) {
        	return response()->json(['code' => 4001, 'msg' => '无可用小票兑换']);
        }

        $rmb = sprintf("%.2f", ($user_receipt / $this->receipt_rmb_rate) );
        if ( $rmb  < 1 ) {
            return response()->json(['code' => 4001, 'msg' => '20小票起兑']);
        }

        DB::beginTransaction();

        UserAccount::out( UserAccount::BUSINESS_EXCHANGE, 0, $user->id, UserAccount::RECEIPT, $user_receipt, '小票兑换人民币' );
        // $user_receipt->receipt = 0;
        // $user_receipt->save();

        while ( $rmb > 200 ) {
            $userExchange = new UserExchange();
            $userExchange->user_id = $user->id;
            $userExchange->rmb = 200;
            $userExchange->state = 0;
            $userExchange->save();

            $rmb -= 200;
        }

        $userExchange = new UserExchange();
        $userExchange->user_id = $user->id;
        $userExchange->rmb = $rmb;
        $userExchange->state = 0;
        $userExchange->save();

        DB::commit();

        $totalrmb = UserExchange::where('user_id', $user->id)->where('state', 0)->sum('rmb');

        return response()->json(['code' => 1000, 'data' => ['rmb' => $totalrmb, 'receipt' => 0]]);
    }

    //发红包
    public function send( Request $request )
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ( empty( $user->public_openid ) ) {
            return response()->json(['code' => 40021, 'msg' => '请关注公众号轮盘视频']);  
        }

        $exchange_list = UserExchange::where('user_id', $user->id)->where('state', 0)->get();

        DB::beginTransaction();

        try {
        	foreach ($exchange_list as $key => $item) {
        		$item->state = 1;
        		$ret = $item->save();
                if ( $ret ) {
                    $data = app(Third\WechatController::class)->sendLuckyMoney($user->id, $item->id, $item->rmb);
                    if ( $data['result_code'] != 'SUCCESS' ) {
                        throw new \Exception();
                    }    
                }
	        }
        } catch (Exception $e) {
        	DB::rollback();
        	return response()->json(['code' => 40022, 'msg' => 'send_hongbao_error']);	
        }

        DB::commit();

        return response()->json(['code' => 1000]);
    }

    public function test( Request $request )
    {
    	$user = JWTAuth::parseToken()->authenticate();
    	$data = app(Third\WechatController::class)->sendLuckyMoney($user->id, 2, 1);
    	var_dump( $data->return_code );
    }
}
     