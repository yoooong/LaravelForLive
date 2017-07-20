<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\UserCount;
use App\UserReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * 小票
 */
class ReceiptController extends Controller
{
    const RECEIPT_RATE = 2;  //小票
    
    public function count()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $data['receipt'] = UserAccount::total($user->id, UserAccount::RECEIPT );

        return response()->json(['code' => 1000, 'data' => $data]);
    }

    public function zslist()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $data['receipt'] = UserAccount::total($user->id, UserAccount::RECEIPT );
        $data['list'] = $this->rate();

        return response()->json(['code' => 1000, 'data' => $data]);
    }

    public function rate()
    {
        return [
            ['zs' => '100', 'rate' => self::RECEIPT_RATE],
            ['zs' => '300', 'rate' => self::RECEIPT_RATE],
            ['zs' => '1000', 'rate' => self::RECEIPT_RATE],
            ['zs' => '2000', 'rate' => self::RECEIPT_RATE],
            ['zs' => '5000', 'rate' => self::RECEIPT_RATE],
        ];
    }

    //钻石兑换小票
    public function exchange(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $zs = $request->input('zs');

        $user_zs = UserAccount::total($user->id, UserAccount::ZS );

        if ( $zs <= 0 ) {
        	return response()->json(['code' => 9000, 'msg' => '兑换钻石数必须大于0']);
        }

        if ($user_zs < $zs) {
            return response()->json(['code' => 9000, 'msg' => 'not_enought_zs']);
        }

        try {
            DB::beginTransaction();

            $receipt = $zs * self::RECEIPT_RATE;

            UserAccount::out( UserAccount::BUSINESS_EXCHANGE, 0, $user->id, UserAccount::ZS, $zs, '钻石兑换小票' );
            UserAccount::in(  UserAccount::BUSINESS_EXCHANGE, 0, $user->id, UserAccount::RECEIPT, $receipt, '钻石兑换小票' );

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['code' => 9000, 'msg' => 'exchange_error']);
        }

        return response()->json(['code' => 1000, 'data' => ['receipt' => UserAccount::total($user->id, UserAccount::RECEIPT )]]);
    }
}
     