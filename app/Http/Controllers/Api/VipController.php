<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Vip;
use Tymon\JWTAuth\Facades\JWTAuth;

class VipController extends Controller
{
    public function all()
    {
    	$user = JWTAuth::parseToken()->authenticate();

        $list = Vip::get();
        $data = [];
        foreach ($list as $key => $item) {
        	$icon = $item->icon_false;
        	$state = 0;
        	if ( $user->level >= $item->id ) {
        		$icon = $item->icon_true;
        		$state = 1;
        	}
        	$data[] = [
        		'id' => $item->id,
                'name' => $item->name,
        		'name_en' => $item->name_en,
        		'icon' => $icon,
        		'state' => $state,
        		'price'	=> $item->price
        	];
        }

        return response()->json(['code' => 1000, 'data' => $data]);
    }
}
     