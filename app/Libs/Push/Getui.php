<?php
namespace App\Libs\Push;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;
//服务端推送
class Getui
{
    public static function serverPush( $cid, $title, $content, $data = '', $payload )
    {
        define('APPKEY', env( 'GETUI_APPKEY' ));
        define('APPID', env( 'GETUI_APPID' ));
        define('MASTERSECRET', env('GETUI_MASTERSECRET') );
        define('HOST','http://sdk.open.api.igexin.com/apiex.htm');

        $igt = new \IGeTui(HOST, APPKEY, MASTERSECRET);
    
        $template = self::IGtTransmissionTemplateDemo( $title, $content, $data, $payload );

        //个推信息体
        $message = new \IGtSingleMessage();

        $message->set_isOffline(true);//是否离线
        $message->set_offlineExpireTime(3600*12*1000);//离线时间
        $message->set_data($template);//设置推送消息类型
        $message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        //接收方
        $target = new \IGtTarget();
        $target->set_appId(APPID);
        $target->set_clientId($cid);
        
        return $igt->pushMessageToSingle($message,$target);
    }

    public static function IGtTransmissionTemplateDemo( $title, $content, $data, $payload ){
        $template =  new \IGtTransmissionTemplate();
        $template->set_appId(APPID);//应用appid
        $template->set_appkey(APPKEY);//应用appkey
        $template->set_transmissionType(1);//透传消息类型
        $template->set_transmissionContent( $data );//透传内容

        //APN高级推送
        $apn = new \IGtAPNPayload();
        $alertmsg=new \DictionaryAlertMsg();
        $alertmsg->body = $content ;
        $alertmsg->actionLocKey = "ActionLockey";
        $alertmsg->locKey = "LocKey";
        $alertmsg->locArgs = array("locargs");
        $alertmsg->launchImage = "launchimage";
           // IOS8.2 支持
        $alertmsg->title = $title;
        $alertmsg->titleLocKey="TitleLocKey";
        $alertmsg->titleLocArgs=array("TitleLocArg");

        $apn->alertMsg=$alertmsg;
        $apn->badge=7;
        $apn->sound="";
        $apn->add_customMsg("payload", $payload);
        $apn->contentAvailable=1;
        $apn->category="ACTIONABLE";
        $template->set_apnInfo($apn);

        return $template;
    }

    public static function IGtNotificationTemplateDemo(){
        $template =  new \IGtNotificationTemplate();
        $template->set_appId(APPID);//应用appid
        $template->set_appkey(APPKEY);//应用appkey
        $template->set_transmissionType(1);//透传消息类型
        $template->set_transmissionContent("测试离线");//透传内容
        $template->set_title("个推");//通知栏标题
        $template->set_text("个推最新版点击下载");//通知栏内容
        $template->set_logo("http://wwww.igetui.com/logo.png");//通知栏logo
        $template->set_isRing(true);//是否响铃
        $template->set_isVibrate(true);//是否震动
        $template->set_isClearable(true);//通知栏是否可清除
        // iOS推送需要设置的pushInfo字段
        //$template ->set_pushInfo($actionLocKey,$badge,$message,$sound,$payload,$locKey,$locArgs,$launchImage);
        //$template ->set_pushInfo("test",1,"message","","","","","");
        return $template;
    }	
}