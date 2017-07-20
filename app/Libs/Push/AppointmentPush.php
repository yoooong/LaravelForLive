<?php
namespace App\Libs\Push;

use App\AppointmentOrder;
use App\Http\Controllers\Api\Third\TimController;
use App\Libs\Push\Getui;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
//服务端推送
class AppointmentPush
{
	const SOCKET_PUSH_SERVER = '127.0.0.1:9501';

	public static function job( $user_id, $data )
	{
		$appointmentOrder = AppointmentOrder::find( $data['id'] );

		$orderUser = User::find( $appointmentOrder->user_id );
		$targetUser = User::find( $appointmentOrder->target_user_id ); 

		$data['uuid'] = $orderUser->uuid;

		try {
			app( TimController::class )->openim_send_msg( $targetUser->uuid, $orderUser->uuid, '订单已收到，会尽快确认' );
		} catch (\Exception $e) {
		}

		try {
			$text = '您有新的订单，请15分钟内确认';
			$data = [
	            'type' => 3,
	            'data' => [
	                'order_id' => $data['id']
	            ]
	        ];
			Getui::serverPush( $targetUser->cid, $text, $text, json_encode( $data ), $data );
		} catch (\Exception $e) {
		}	
		
		$client = new Client;
		return $client->request('POST', self::SOCKET_PUSH_SERVER, 
			['form_params' => ['data' => $data, 'user_id' => $user_id]]
		);
	}
}