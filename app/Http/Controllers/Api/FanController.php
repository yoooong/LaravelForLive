<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Third\TimController;
use App\Http\Controllers\Controller;
use App\User;
use App\UserFan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class FanController extends Controller
{
    public function query()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $fans = $user->fans()->get();

        return response()->json(['code' => 1000, 'data' => $fans]);
    }

    public function idols()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $fans = $user->idols()->get();

        return response()->json(['code' => 1000, 'data' => $fans]);
    }

    public function remember(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $toUserUuid = $request->input('user');
        $type = $request->input('type');
        $toUser = User::where('uuid', $toUserUuid)->first();

        if (!$user->level) {
            return response()->json(['code' => 9000, 'msg' => 'not_vip']);
        }

        try {

            DB::beginTransaction();

            if (!$user->fans()->where('fan_user_id', $toUser->id)->count()) {
                $userFan = new UserFan;
                $userFan->user_id = $user->id;
                $userFan->fan_user_id = $toUser->id;
                $userFan->save();

                //安卓不推送融云消息
                // if ( $type != 1 ) {
                    // $data = app(Third\RongyunController::class)->publish($toUser->id, $user->id);
                    // if ($data['code'] != 200) {
                    //     throw new \Exception();
                    // }
                    
                // }
                //腾讯im添加好友
                $data = app(TimController::class)->friend_import( $user->uuid, $toUser->uuid );
                if ( $data['ActionStatus'] != 'OK' ) {
                    throw new \Exception();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['code' => 9000, 'msg' => 'create_fan_error']);
        }

        return response()->json(['code' => 1000]);
    }
}
