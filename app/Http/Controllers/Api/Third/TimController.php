<?php
namespace App\Http\Controllers\Api\Third;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use TimRestAPI;

class TimController extends Controller 
{
	protected $appid;
	protected $identifier;
	protected $signature;
	protected $private_pem_path;

	protected $api;

	public function __construct()
    {
        $this->appid = env('QQIM_APPID');
        $admin_identifier = env('QQIM_IDENTIFIER');
        $this->signature = env('QQIM_SIGNATURE');
        $this->private_pem_path = env('QQIM_PRIVATE_KEY_PATH');

        $this->api = new TimRestAPI;
		$this->api->init($this->appid, $admin_identifier);
		$this->generate_user_sig( $admin_identifier );
    }

    public function generate_user_sig( $identifier )
    {
    	$ret = $this->api->generate_user_sig($identifier, '36000', app_path($this->private_pem_path), app_path($this->signature));

    	if($ret == null || strstr($ret[0], "failed")){
    		return null;
    	}

    	return $ret[0];
    }

    public function account_import($identifier, $nick, $face_url)
    {
    	$ret = $this->api->account_import($identifier, $nick, $face_url);

        return $ret;
    }

    public function friend_import( $account_id, $receiver )
    {
    	$ret = $this->api->sns_friend_import($account_id, $receiver);
    	
		return $ret;
    }

    public function openim_send_msg( $account_id, $receiver, $text_content )
    {
        $ret = $this->api->openim_send_msg( $account_id, $receiver, $text_content );

        return $ret;
    }
}
     