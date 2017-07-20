<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use EasyWeChat\Message\Image as WMImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Tymon\JWTAuth\Facades\JWTAuth;

class QrcodeController extends Controller
{
    public function create(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $wechat = app('wechat');

        $qrcode = $wechat->qrcode;
        $data = $qrcode->temporary($user->id, 2592000);

        $source = QrCode::format('png')->size(225)->margin(1)->generate($data->url);

        $file = 'upfiles/qrcode/' . $user->id . '.png';

        $path = public_path($file);

        Log::debug($path);
        
        Image::make(public_path('images/qrcode-bg.png'))->insert($source, 'top-left', 133, 121)->save($path);

        $material = $wechat->material;
        $data = $material->uploadImage($path);
        $image = new WMImage(['media_id' => $data->media_id]);

        if($request->input('type') == 'link'){
            return response()->json(['code'=>1000,'data'=>$file]);
        } else {
            $wechat->staff->message($image)->to($user->public_openid)->send();
        }
    }
}
