<?php
namespace App\Http\Controllers\Api;

use App\AppointmnetUserScore;
use App\Http\Controllers\Controller;
use App\Tag;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Facades\JWTAuth;

class OnlineController extends Controller
{
    public function users(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $tag = $request->input('tag');
        $count = $request->input('count') ?: 11;

        if ($tag) {
            $tag = Tag::find($tag);
            $query = $tag->users()->where('user_id', '<>', $user->id)->inRandomOrder();
        } else {
            $query = User::where('id', '<>', $user->id)->inRandomOrder();
        }

        // $data['score'] = AppointmnetUserScore::where('target_user_id', $user->id)->
        // select('user_id', 'avatar','nickname','comment', 'score', 'created_at')->
        // orderBy('created_at','desc')->limit(3)->get();
        $data = $query->take($count)->get();

        $cur_time = time();

        foreach ($data as $item) {
            $scoreQuery = AppointmnetUserScore::select('comment','avatar','nickname', 'score', 'created_at')->orderBy('created_at','desc')->limit(3);
            $item->top_three = $scoreQuery->where('target_user_id', $item->id)->get();
            $item->comment_count = AppointmnetUserScore::where('target_user_id',$item->id)->count();
            
            $last_time = Redis::get('lpsp_online_users_set.online_time_'.$item->id );
            $item->login_second = $last_time ? $cur_time - $last_time : 86400;
        }

        return response()->json(['code' => 1000, 'data' => $data]);
    }

    public function tag()
    {
        $data = Tag::select('id', 'name')->get();
        return response()->json(['code' => 1000, 'data' => $data]);
    }

    //评论列表

    public function scoreList(Request $request)
    {
        $target_user_id = $request->input('id');
        $page_count = $request->input('count') ?$request->input('count') :10;


        $data = AppointmnetUserScore::where('target_user_id',$target_user_id)->
            select('nickname','avatar','score','comment','created_at')->
            orderBy('created_at','desc')->paginate($page_count);

        return response()->json(['code'=>1000,'data'=>$data]);

    }
}
     