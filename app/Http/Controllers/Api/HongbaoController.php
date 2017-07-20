<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use App\UserAccount;
use App\UserHongbao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class HongbaoController extends Controller 
{
	public function code( Request $request )
	{
		$user         = JWTAuth::parseToken()->authenticate();
		$id           = $request->input('to_hongbao');
		$to_user_uuid = $request->input('to_user');

		$to_user = User::where('uuid', $to_user_uuid)->first();
		$hongbao = UserHongbao::findOrFail( $id );

		if ( $hongbao->to_user_id != $to_user->id || 
			 $hongbao->user_id != $user->id ) {
			return response()->json(['code' => 1001, 'msg' => '用户不正确']);
		}

		return response()->json(['code' => 1000, 'data' => $hongbao->code]);
	}

	public function get( Request $request )
	{
		$user         = JWTAuth::parseToken()->authenticate();
		$id           = $request->input('hongbao');
		$fd           = $request->input('fd');
		$code         = $request->input('code');
		
		$todaycount = UserHongbao::where('user_id', $user->id)->where('status', 1)->where('create_date', date('Y-m-d'))->count();
		if ( $todaycount > 3 ) {
			return response()->json(['code' => 1001, 'msg' => '当天红包已领完']);
		}

		$hongbao = UserHongbao::findOrFail( $id );

		if ( $hongbao->user_id != $user->id || 
			 $hongbao->fd != $fd ) {
			return response()->json(['code' => 1001, 'msg' => '非法请求']);
		}

		//90秒后才可打开
		if ( $hongbao->create_time + 30 > time() ) {
			return response()->json(['code' => 1002, 'msg' => '未到时间打开']);
		}

		//已领取
		if ( $hongbao->status == 1 ) {
			return response()->json(['code' => 1003, 'msg' => '红包已领过']);
		}

		if ( $hongbao->code !== $code ) {
			return response()->json(['code' => 1004, 'msg' => '口令错误，请询问对方正确的口令哦']);
		}

		$hongbao->status = 2; //待分享领取
		$hongbao->save();

		return response()->json(['code' => 1000, 'money' => $hongbao->value, 'msg' => '分享方可使用红包']);
	}

	public function shareUnlock(Request $request){
        $user         = JWTAuth::parseToken()->authenticate();
        $HongbaoId = $request->input('id');

        $hongbao = UserHongbao::findorfail($HongbaoId);

        if ( $hongbao->user_id != $user->id ) {
			return response()->json(['code' => 1001, 'msg' => '非法请求']);
		}

		if ( $hongbao->status == 1 ) {
			return response()->json(['code' => 1003, 'msg' => '红包已领过']);
		}

		if ( $hongbao->status !== 2 ) {
			return response()->json(['code' => 1003, 'msg' => '请输入密码并分享可使用红包']);
		}

		$todaycount = UserHongbao::where('user_id', $user->id)->where('status', 1)->where('create_date', date('Y-m-d'))->count();
		if ( $todaycount > 3 ) {
			return response()->json(['code' => 1001, 'msg' => '当天红包已领完']);
		}

        DB::beginTransaction();

        $hongbao->status = 1;
        $ret = $hongbao->save();

        if ( $ret ) {
            try {
                UserAccount::in( UserAccount::BUSINESS_CODE_HONGBAO, $hongbao->id, $user->id, UserAccount::RECEIPT, $hongbao->value, '口令红包' );
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['code' => 1005, 'msg' => '系统繁忙，请频繁操作']);
            }
        }
        return response()->json(['code' => 1000, 'money' => $hongbao->value]);
    }
}
     