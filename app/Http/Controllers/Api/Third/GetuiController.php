<?php
namespace App\Http\Controllers\Api\Third;

use App\Http\Controllers\Controller;
use App\Libs\Push\Getui;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class GetuiController extends Controller 
{
	public function bind( Request $request )
	{
		$cid = $request->input('cid', 'string');
		$user = JWTAuth::parseToken()->authenticate();
		if ( $user->cid == $cid ) {
			return response()->json(['code' => 1001]);
		}
		$user->cid = $cid;
		$user->save();

		return response()->json(['code' => 1000]);
	}
}