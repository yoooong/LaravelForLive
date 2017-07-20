<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Third\RongyunController;
use App\Http\Controllers\Api\Third\TimController;
use App\Http\Controllers\Controller;
use App\User;
use App\UserAccount;
use App\UserCount;
use App\UserReceipt;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    const API_OAUTH_GET = 'https://api.weixin.qq.com/sns/userinfo';

    public function wechat(Request $request)
    {
        $input = $request->only('access_token', 'openid');
        $input['lang'] = 'zh_CN';

        $client = new Client;
        $response = $client->request('GET', self::API_OAUTH_GET, ['query' => $input]);

        $body = $response->getBody();
        $data = json_decode($body, true);

        if (array_key_exists('errcode', $data)) {
            return response()->json(['code' => 9000, 'msg' => $data['errmsg']]);
        } else {
            $openid = $data['openid'];
            $unionid = $data['unionid'];

            if ($user = User::where('openid', $openid)->first()) {
            } elseif ($user = User::where('unionid', $unionid)->first()) {
                $user->openid = $openid;
                $user->save();
            } else {
                $user = new User;
                $user->uuid = Uuid::uuid1();
                $user->unionid = $unionid;
                $user->openid = $openid;
                $user->public_openid = '';
                $user->avatar = $data['headimgurl'];
                $user->nickname = $data['nickname'];
                $user->sex = $data['sex'];
                $user->country = $data['country'];
                $user->province = $data['province'];
                $user->city = $data['city'];
                $user->signature = '';
                $user->password = '';
                $user->save();

                $userCount = new UserCount;
                $user->count()->save($userCount);

                $userReceipt = new UserReceipt();
                $user->receipt()->save($userReceipt);

                // app(RongyunController::class)->generate($user->id);
                //导入IM用户
                app( TimController::class )->account_import( $user->uuid, $user->nickname, $user->avatar );
            }

            try {
                if (!$token = JWTAuth::fromUser($user)) {
                    return response()->json(['code' => 9000, 'msg' => 'invalid_credentials']);
                }
            } catch (JWTException $e) {
                return response()->json(['code' => 9000, 'msg' => 'could_not_create_token']);
            }

            $data = ['token' => $token];
            foreach (['uuid', 'avatar', 'nickname', 'level'] as $key) {
                $data[$key] = $user->{$key};
            }

            $sig = app( TimController::class )->generate_user_sig( $user->uuid );
            $data['sig'] = $sig;

            return response()->json(['code' => 1000, 'data' => $data]);
        }

    }

    public function mobile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 9000, 'msg' => $validator->errors()->first()]);
        }

        $numbers = array(1 => '13012345678', 2 => '13811112222', 3 => '13833334444');

        $mobile = $request->input('mobile');
        $password = $request->input('password');

        if (!(in_array($mobile, $numbers) && $password == '123456')) {
            return response()->json(['code' => 9000, 'msg' => 'invalid_mobile_or_password']);
        }

        $user = User::find(array_search($mobile, $numbers));
        $token = JWTAuth::fromUser($user);

        $data = ['token' => $token];
        foreach (['uuid', 'avatar', 'nickname'] as $key) {
            $data[$key] = $user->{$key};
        }

        return response()->json(['code' => 1000, 'data' => $data]);
    }

    public function user()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $data = $user->toArray();
        // $data['zs'] = $user->count ? $user->count->ZS : 0;
        // $data['receipt'] = $user->receipt ? $user->receipt->receipt : 0;
        $data['zs'] = UserAccount::total($user->id, UserAccount::ZS );
        $data['receipt'] = UserAccount::total($user->id, UserAccount::RECEIPT );

        return response()->json(['code' => 1000, 'data' => $data]);
    }

    //发送验证码
    public function sendcode( Request $request )
    {
        $phone = trim($request->input('phone'));

        if ( !preg_match("/1[34578]{1}\d{9}$/", preg_replace("/\s/","", trim($phone))) ) {
            return response()->json(['code' => 1001, 'msg' => '请填写正确的手机号']);
        }

        $key = 'user_send_code_' . $phone;
        if ( Redis::ttl( $key ) > 60 ) {
            return response()->json(['code' => 1002, 'msg' => '请60秒后再发起验证码']);
        }

        $code = rand( 100000, 999999 );

        Redis::setex($key, 120, $phone.'_'.$code );

        try {
            $message = '您的验证码是'. $code .'，有效期2分钟，若非本人操作，请勿泄露。【轮盘视频】';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://sms-api.luosimao.com/v1/send.json");
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, 'api:key-5145112f10b704bbd5592f80402c77b5');
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $phone, 'message' => $message));
            $res = curl_exec($ch);
            curl_close( $ch );
        } catch (\Exception $e) {
        }
        
        return response()->json(['code' => 1000, 'res' => $res]);
    }

    // public function mobile( Request $request )
    // {
    //     $phone = trim($request->input('phone'));
    //     $code  = $request->input('code');

    //     if ( !preg_match("/1[34578]{1}\d{9}$/", preg_replace("/\s/","", $phone)) ) {
    //         return response()->json(['code' => 1001, 'msg' => '请填写正确的手机号']);
    //     }

    //     $key = 'user_send_code_' . $phone;

    //     if ( !Redis::exists( $key )  ) {
    //         return response()->json(['code' => 1001, 'msg' => '该验证码已过期，请重新获取验证码']);
    //     }

    //     if ( Redis::get( $key ) != $phone.'_'.$code ) {
    //         return response()->json(['code' => 1002, 'msg' => '验证码错误']);
    //     }

    //     $user = User::where('phone', $phone)->first();

    //     if ( !$user ) {
    //         $user = new User;
    //         $user->uuid = Uuid::uuid1();
    //         $user->unionid = '';
    //         $user->openid = '';
    //         $user->public_openid = '';
    //         $user->avatar = '';
    //         $user->nickname = '游客';
    //         $user->sex = 0;
    //         $user->country = '';
    //         $user->province = '';
    //         $user->city = '';
    //         $user->signature = '';
    //         $user->password = '';
    //         $user->phone = $phone;
    //         $user->save();

    //         $userCount = new UserCount();
    //         $user->count()->save($userCount);

    //         $userReceipt = new UserReceipt();
    //         $user->receipt()->save($userReceipt);
    //     }

    //     $token = JWTAuth::fromUser($user);

    //     $data = ['token' => $token];
    //     foreach (['uuid', 'avatar', 'nickname'] as $key) {
    //         $data[$key] = $user->{$key};
    //     }

    //     return response()->json(['code' => 1000, 'data' => $data]);
    // }
}
