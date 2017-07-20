<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Tag;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Facades\JWTAuth;

class SearchController extends Controller 
{
	public function user( Request $request )
	{
		$user = JWTAuth::parseToken()->authenticate();
		$sex   = $request->input('sex');
		$level = $request->input('level');
		$price = $request->input('price');
		$name  = $request->input('name');
		$tag = $request->input('tag');

		if ($tag) {
            $tag = Tag::find($tag);
            $query = $tag->users()->where('user_id', '<>', $user->id);
        } else {
        	$query = User::where('id', '<>', $user->id);	
        }

		if ( $sex > 0 ) $query->where('sex', $sex);
		if ( $level > 0 ) $query->where('level', $level);
		if ( $price > 0 ) $query->where('chat_price', $price);
		if ( !empty( $name ) ) $query->where('nickname', 'like', "%$name%");

		$datas = $query->select('user.id', 'uuid', 'nickname', 'avatar', 'sex', 'signature', 'chat_price', 'country', 'province', 'city')->paginate(10);

		$cur_time = time();
		foreach ($datas as $key => $data) {
			$last_time = Redis::get('lpsp_online_users_set.online_time_'.$data->id );
			$data->login_second = $last_time ? $cur_time - $last_time : 86400;
		}

		return response()->json(['code' => 1000, 'data' => $datas]);
	}
}