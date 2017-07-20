<?php
namespace App\Http\Controllers\Api;
use App\Complain;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ComplainController extends Controller 
{
	public function add( Request $request )
	{
		$user = JWTAuth::parseToken()->authenticate();

		$uuid = $request->input('user');
		$reason = $request->input('reason');

		$complain_to_user = User::where('uuid', $uuid)->first();

		$complain = new Complain();
		$complain->user_id = $complain_to_user->id;
		$complain->reason = $reason;
		$complain->save();		

		return response()->json(['code' => 1000]);
	}
}
     