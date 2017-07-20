<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Api\Third\RongyunController;
use App\User;
use App\UserCount;
use App\UserReceipt;
use Illuminate\Contracts\Auth\Guard;
use Ramsey\Uuid\Uuid;
use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class Authenticate
{
    protected $auth;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $oauth_user = session('wechat.oauth_user');

        $openid = $oauth_user['id'];
        $unionid = $oauth_user['original']['unionid'];

        if($this->auth->guest()) {
            if ($user = User::where('public_openid', $openid)->first()) {
            } elseif ($user = User::where('unionid', $unionid)->first()) {
                $user->public_openid = $openid;
                $user->save();
            } else {
                $user = new User;
                $user->uuid = Uuid::uuid1();
                $user->unionid = $unionid;
                $user->openid = '';
                $user->public_openid = $openid;
                $user->avatar = $oauth_user['original']['headimgurl'];
                $user->nickname = $oauth_user['nickname'];
                $user->sex = $oauth_user['original']['sex'];
                $user->country = $oauth_user['original']['country'];
                $user->province = $oauth_user['original']['province'];
                $user->city = $oauth_user['original']['city'];
                $user->signature = '';
                $user->password = '';
                $user->save();

                $userCount = new UserCount;
                $user->count()->save($userCount);

                $userReceipt = new UserReceipt();
                $user->receipt()->save($userReceipt);

                app(RongyunController::class)->generate($user->id);
            }

            $this->auth->login($user);

            $token = JWTAuth::fromUser($user);
            session(['token' => $token]);
        }

        return $next($request);
    }
}
