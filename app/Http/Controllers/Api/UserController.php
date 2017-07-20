<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Third\TimController;
use App\Http\Controllers\Controller;
use App\Product;
use App\User;
use App\UserAccount;
use App\UserCount;
use App\UserReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{

    public function info(Request $request)
    {
    //     $user = User::where('uuid', $request->input('uuid'))->select(['id','uuid', 'avatar', 'nickname', 'age', 'sex', 'sexual', 'constell', 'marital', 'chat_price','country', 'province', 'level','city', 'signature'])->first();

        $user = User::where('uuid', $request->input('uuid'))->select(['id','nickname'])->first();
        // dd($user);
        // if (!$user) {
        //     return response()->json(['code' => 9000, 'msg' => 'not_user_found']);
        // }
        // $user_id = $user->id;
        // $user = $user->toArray();
        // $user['count'] = UserAccount::total($user_id, UserAccount::ZS );
        // $user['receipt'] = UserAccount::total($user_id, UserAccount::RECEIPT );
        // $user['unit_price'] = Product::find(12)->price;

        return response()->json(['code' => 1000, 'data' => $user]);
    }

    public function update(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $data = [];
        $fields = ['nickname', 'age', 'sex', 'sexual', 'constell', 'marital', 'country', 'province', 'city', 'signature'];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->input($field);
            }
        }

        try {
            DB::beginTransaction();

            $user->update($data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['code' => 9000, 'msg' => 'update_user_error']);
        }

        return response()->json(['code' => 1000]);
    }

    public function center()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $data['nickname'] = $user->nickname;
        $data['avatar'] = $user->avatar;
        $data['fans'] = $user->fans->count();
        $data['zs'] = UserAccount::total($user->id, UserAccount::ZS );
        $data['receipt'] = UserAccount::total($user->id, UserAccount::RECEIPT );

        return response()->json(['code' => 1000, 'data' => $data]);
    }

    public function timsig()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $sig = app( TimController::class )->generate_user_sig( $user->uuid );

        return response()->json(['code' => 1000, 'data' => $sig]);
    }

    //绑定手机号
    public function bindmobile( Request $request )
    {
        $user = JWTAuth::parseToken()->authenticate();

        $phone = trim($request->input('phone'));
        $code  = $request->input('code');

        if ( !preg_match("/1[34578]{1}\d{9}$/", preg_replace("/\s/","", $phone)) ) {
            return response()->json(['code' => 1001, 'msg' => '请填写正确的手机号']);
        }

        $key = 'user_send_code_' . $phone;

        if ( !Redis::exists( $key )  ) {
            return response()->json(['code' => 1001, 'msg' => '该验证码已过期，请重新获取验证码']);
        }

        if ( Redis::get( $key ) != $phone.'_'.$code ) {
            return response()->json(['code' => 1002, 'msg' => '验证码错误']);
        }

        if (User::where('phone', $phone)->exists()) {
            return response()->json(['code' => 1003, 'msg' => '该手机号已绑定其他账户']);
        }

        $user->phone = $phone;
        $user->save();

        return response()->json(['code' => 1000]);
    }

    //绑定微信号
    public function bindwechat( Request $request )
    {
        $user = JWTAuth::parseToken()->authenticate();

        $input = $request->only('access_token', 'openid');
        $input['lang'] = 'zh_CN';

        $client = new Client;
        $response = $client->request('GET', self::API_OAUTH_GET, ['query' => $input]);

        $body = $response->getBody();
        $data = json_decode($body, true);

        if (array_key_exists('errcode', $data)) {
            return response()->json(['code' => 3001, 'msg' => $data['errmsg']]);
        }

        $openid = $data['openid'];
        $unionid = $data['unionid'];

        if (User::where('openid', $openid)->exists()) {
            return response()->json(['code' => 3002, 'msg' => '该微信号已存在']);
        } elseif (User::where('unionid', $unionid)->exists()) {
            return response()->json(['code' => 3002, 'msg' => '该微信号已存在']);
        }

        $user->openid = $openid;
        $user->unionid = $unionid;
        $user->save();
        
        return response()->json(['code' => 1000]);
    }
}
