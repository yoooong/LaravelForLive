<?php
namespace App\Http\Controllers\Api;
use App\Blacklist;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class BlacklistController extends Controller
{
    public function all(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $blacklist = $user->blacklist()->paginate(1);

        return response()->json(['code' => 1000, 'data' => $blacklist]);
    }

    public function add(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $to_user_id = $request->input('user_id');

        $to_user = User::where('uuid', $to_user_id)->first();

        try {
            DB::beginTransaction();

            if (!$user->blacklist()->where('to_user_id', $to_user->id)->count()) {
                $blacklist = new Blacklist();
                $blacklist->user_id = $user->id;
                $blacklist->to_user_id = $to_user->id;
                $blacklist->save();

                $data = app(Third\RongyunController::class)->black($user->id, $to_user->id);
                if ($data['code'] != 200) {
                    throw new \Exception();
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            return response()->json(['code' => 9000, 'msg' => 'create_black_error']);
        }

        return response()->json(['code' => 1000]);
    }
}
     