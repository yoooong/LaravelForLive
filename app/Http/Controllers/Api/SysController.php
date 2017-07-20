<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class SysController extends Controller
{
	public function conf()
	{
		$data['switch'] = true;
		return response()->json($data);
	}
}