<?php
namespace App\Http\Controllers\Api;

use App\AppointmentOrder;
use App\AppointmnetUserScore;
use App\Blacklist;
use App\Http\Controllers\Api\Third\TimController;
use App\Http\Controllers\Controller;
use App\Order;
use App\OrderItem;
use App\Product;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\Facades\JWTAuth;

class AppointmentController extends Controller 
{
	const comfirm_time = 15*60;

	public function create( Request $request )
	{
		$user = JWTAuth::parseToken()->authenticate();

		$time         = date('Y-m-d H:i:s', $request->input('time'));
		$num          = $request->input('num');
		$remark       = $request->input('remark');
		$to_user_uuid = $request->input('to_user_id');

		if ( $num <= 0 ) {
			return response()->json(['code' => 1001, 'msg' => '数量必须大于0']);
		}

		//黑名单用户禁止下单 TODO

		$productId = 12;
		$product = Product::findOrFail($productId);

		$to_user = User::where('uuid', $to_user_uuid)->first();
		if ( !$to_user ) {
			return response()->json(['code' => 1002, 'msg' => '用户不存在']);
		}

		if ( $to_user->chat_price <= 0 ) {
			return response()->json(['code' => 1003, 'msg' => '该用户不能被下单']);
		}

		$black_user = Blacklist::where('user_id',$to_user->id)->where('to_user_id',$user->id)->first();
		if ($black_user) {
		    return response()->json(['code'=> 1009, 'msg' => "您已被对方拉黑，禁止下单"]);
        }

        $price = $to_user->chat_price * $num;

        $appointmentOrder = new AppointmentOrder;
        $appointmentOrder->order_id = 0;
        $appointmentOrder->user_id = $user->id;
        $appointmentOrder->target_user_id = $to_user->id;
        $appointmentOrder->order_time = $time;
        $appointmentOrder->product_id = $productId;
        $appointmentOrder->num = $num;
        $appointmentOrder->status = 0;
        $appointmentOrder->payed = 0;
        $appointmentOrder->remark = $remark;
        $appointmentOrder->price = $price;
        $appointmentOrder->save();


        return response()->json(['code' => 1000, 'data' => ['id' => $appointmentOrder->id, 'total' => $appointmentOrder->price]]);
	}

	public function jobs()
	{
		$user = JWTAuth::parseToken()->authenticate();

		$orders = AppointmentOrder::where('target_user_id', $user->id)
								  ->where('status', '0' )
								  ->where('created_at', '>', Carbon::now()->subMinutes(15) )
								  ->where('payed', 1)
								  ->select('id', 'user_id', 'order_time', 'payed_time')
								  ->get();
		
		$datas = [];
		foreach ($orders as $order) {
			$user = User::select('nickname', 'avatar')->find( $order->user_id );
			$datas[] = [
				'id' => $order->id,
				'order_time' => $order->order_time,
				'nickname' => $user->nickname,
				'avatar'   => $user->avatar,
				'uuid'		=> $user->uuid,
				'payed_time' => $order->payed_time
			];
		}

		return response()->json(['code' => 1000, 'data' => $datas]);
	}

	public function detail( Request $request )
	{
		$user = JWTAuth::parseToken()->authenticate();
		$id   = $request->input('id');

		$appointmentOrder = AppointmentOrder::findOrFail( $id );

		if ( $appointmentOrder->user_id == $user->id ) {
			$show_user = User::find( $appointmentOrder->target_user_id );
		} elseif ( $appointmentOrder->target_user_id == $user->id ) {
			$show_user = User::find( $appointmentOrder->user_id );
		} else {
			return response()->json(['code' => 1001, 'data' => '用户错误']);
		}

		$can_comment = AppointmnetUserScore::where('appointment_order_id', $appointmentOrder->id)->where('user_id', $user->id)->count();

		$data                     = $appointmentOrder->toArray();
		$data['timeout']      	  = ($appointmentOrder->order_time + self::comfirm_time > time() ) ? 0 : 1;
		$data['unit_price']       = $appointmentOrder->price;
		$data['role']             = ( $appointmentOrder->user_id == $user->id ) ? 0 : 1;
		$data['can_comment']	  = $can_comment;
		$data['user']['nickname'] = $show_user->nickname;
		$data['user']['avatar']   = $show_user->avatar;
		$data['user']['level']    = $show_user->level;
		$data['user']['age']      = $show_user->age;
		$data['user']['sex']      = $show_user->sex;
		$data['user']['constell'] = $show_user->constell;
		$data['user']['uuid']	  = $show_user->uuid;
		$data['user']['score']    = AppointmnetUserScore::where('target_user_id', $show_user->id)->avg('score') ?: 0;

		return response()->json(['code' => 1000, 'data' => $data]);
	}

	public function comfirm( Request $request )
	{
		$user = JWTAuth::parseToken()->authenticate();
		$id   = $request->input('id');

		$appointmentOrder = AppointmentOrder::findOrFail( $id );
		if ( $appointmentOrder->target_user_id != $user->id ) {
			return response()->json(['code' => 1001, 'msg' => '非法请求']);
		}

		if ( $appointmentOrder->status == 1 ) {
			return response()->json(['code' => 1002, 'msg' => '已接单']);
		}

		if ( strtotime( $appointmentOrder->created_at ) + self::comfirm_time < time() ) {
			return response()->json(['code' => 1002, 'msg' => '已过了接单时间']);
		}

		$appointmentOrder->status = 1;
		$appointmentOrder->save();

		$orderUser = User::find( $appointmentOrder->user_id );
		$res = app( TimController::class )->openim_send_msg( $user->uuid, $orderUser->uuid, '我已接单，期待与你相见~' );
		
		return response()->json(['code' => 1000, 'msg' => $res['ActionStatus'] ]);
	}

	public function order()
	{
		$user = JWTAuth::parseToken()->authenticate();

		$orders = AppointmentOrder::where('user_id', $user->id)
								->orWhere(function ($query) use ($user) {
					                $query->where('payed', 1)
					                      ->where('target_user_id', $user->id);
					            })->orderBy('id', 'desc')->paginate(10);

		$product = Product::find(12);

		foreach ($orders as $order) {
			$order->unit_price = $product->price;
			$order->role       = ( $order->user_id == $user->id ) ? 0 : 1;
			$order->user;
			$order->target_user;
		}

		return response()->json(['code' => 1000, 'data' => $orders]);
	}

	public function end( Request $request )
	{
		$user = JWTAuth::parseToken()->authenticate();
		$id   = $request->input('id');
		$appointmentOrder = AppointmentOrder::findOrFail( $id );
		if ( $appointmentOrder->user_id != $user->id ) {
			return response()->json(['code' => 1001, 'msg' => '非法请求']);
		}
		$appointmentOrder->status = 3;
		$appointmentOrder->save();
		return response()->json(['code' => 1000]);
	}

	public function score( Request $request )
	{
		$user = JWTAuth::parseToken()->authenticate();
		$id   = $request->input('id');

		$appointmentOrder = AppointmentOrder::findOrFail( $id );

		$isCustomer  = $appointmentOrder->user_id == $user->id;
		$isProducter = $appointmentOrder->target_user_id == $user->id;

		if ( !$isCustomer && !$isProducter ) {
			return response()->json(['code' => 1001, 'msg' => '非法请求']);
		}

		if ( $isCustomer ) {
			$target_user_id = $appointmentOrder->target_user_id;
		} else {
			$target_user_id = $appointmentOrder->user_id;
		}

		$appointmnetUserScore = AppointmnetUserScore::where('appointment_order_id', $appointmentOrder->id)->where('user_id', $user->id)->first();

		if ( $appointmnetUserScore ) {
			return response()->json(['code' => 1002, 'msg' => '已评分']);
		}

		$fields = ['score', 'comment'];
		foreach ($fields as $field) {
			if ( !$request->has($field)) {
				return response()->json(['code' => 1001, 'msg' => '评分或内容不能为空']);
			}
		}

		$userInfo = User::where('id',$user->id)->select('avatar','nickname')->first();

		//消费者会保存订单评分数据
		if ( $isCustomer ) {
			$appointmentOrder->score = $request->input( 'score' );
			$appointmentOrder->comment = $request->input( 'comment' );
			$appointmentOrder->save();			
		}

		$appointmnetUserScore = new AppointmnetUserScore;
		$appointmnetUserScore->appointment_order_id = $appointmentOrder->id;
		$appointmnetUserScore->user_id = $user->id;
		$appointmnetUserScore->target_user_id = $target_user_id;
        $appointmnetUserScore->avatar = $userInfo->avatar;
        $appointmnetUserScore->nickname = $userInfo->nickname;
		$appointmnetUserScore->score = $request->input( 'score' );
		$appointmnetUserScore->comment = $request->input( 'comment' );
		$appointmnetUserScore->save();

		return response()->json(['code' => 1000]);
	}

}