<?php

namespace App\Http\Controllers\Api\Third;

use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class RongyunController extends Controller
{
    protected $appKey;
    protected $appSecret;

    const SERVERAPIURL = 'http://api.cn.ronghub.com';

    const RC_MGS_TYPE = 'RC:TxtMsg';

    public function __construct()
    {
        $this->appKey = env('RONGYUN_KEY');
        $this->appSecret = env('RONGYUN_SECRET');
    }

    public function generate($userId)
    {
        $user = User::findOrFail($userId);

        $publish_params = [
            'userId' => $user->uuid,
            'name' => $user->nickname,
            'portraitUri' => $user->avatar
        ];

        $client = new Client;
        $response = $client->request('POST', self::SERVERAPIURL . '/user/getToken.json', [
            'headers' => self::createHeaders(),
            'form_params' => $publish_params
        ]);

        $body = $response->getBody();
        $data = json_decode($body, true);

        Log::debug($data);

        if ($data['code'] == 200) {
            $user->rongyun_token = $data['token'];
            $user->save();
        }
    }

    private function createHeaders()
    {
        $nonce = Carbon::now()->format('YmdHis') . str_random();
        $timeStamp = time();
        $sign = sha1($this->appSecret . $nonce . $timeStamp);
        return [
            'RC-App-Key' => $this->appKey,
            'RC-Nonce' => $nonce,
            'RC-Timestamp' => $timeStamp,
            'RC-Signature' => $sign,
        ];
    }

    //关注用户后发送系统消息
    public function publish($userId, $toUserId)
    {
        $user = User::findOrFail($userId);
        $to_user = User::findOrFail($toUserId);

        $content = '{}';
        $pushData = '';
        $pushContent = '';

        $publish_params = [
            'fromUserId' => $user->uuid,
            'toUserId' => $to_user->uuid,
            'objectName' => self::RC_MGS_TYPE,
            'content' => $content,
            'pushData' => $pushData,
            'pushContent' => $pushContent
        ];

        $client = new Client;
        $response = $client->request('POST', self::SERVERAPIURL . '/message/system/publish.json', [
            'headers' => self::createHeaders(),
            'form_params' => $publish_params
        ]);

        $body = $response->getBody();
        $data = json_decode($body, true);

        return $data;
    }

    public function black( $userId, $toUserId )
    {
        $user = User::findOrFail($userId);
        $to_user = User::findOrFail($toUserId);

        $publish_params = [
            'userId' => $user->uuid,
            'blackUserId' => $to_user->uuid
        ];

        $client = new Client;
        $response = $client->request('POST', self::SERVERAPIURL . '/user/blacklist/add.json', [
            'headers' => self::createHeaders(),
            'form_params' => $publish_params
        ]);

        $body = $response->getBody();
        $data = json_decode($body, true);

        return $data;
    }
}
