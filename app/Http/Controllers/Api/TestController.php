<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Third\TimController;
use App\Http\Controllers\Controller;
use App\Libs\Push\AppointmentPush;
use App\Libs\Push\Getui;
use App\User;
use App\UserHongbao;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Facades\JWTAuth;

class TestController extends Controller
{
    public function info(Request $request)
    {
        $id = $request->input('id');
        $user = User::where('id', $id)->first();

        $token = JWTAuth::fromUser($user);

        return "Authorization: Bearer $token";
    }

    public function test(Request $request)
    {
        echo $request->fullUrl();
    }

    public function testtim(Request $request)
    {
        $ret = app(TimController::class)->account_import('test', 'test', 'http://wx.qlogo.cn/mmopen/cm50Ks4Xc7FxnqrdBsClia01bHmyeQrGWT3O1qhgzQRFGY4jfH8gHw7UticcIQHTU6OHfF3hfve1mcz9EaMiaaUQ8YyzFLQ9HRT/0');
        var_dump( $ret );

        $ret = app(TimController::class)->generate_user_sig('test');
        var_dump( $ret );

        $ret = app(TimController::class)->friend_import('admin', 'test');
        var_dump( $ret );
    }

    public function serverPush( Request $request )
    {
        $cid = $request->input('cid');
        $content = $request->input('content');

        $pushdata = [
            'type' => 2,
            'data' => [
                'uuid' => 'abc'
            ]
        ];
        $ret = Getui::serverPush( $cid, $content, $content, json_encode( $pushdata ), $pushdata );
        var_dump( $ret );
    }

}
