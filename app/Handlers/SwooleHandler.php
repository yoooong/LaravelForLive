<?php 
namespace App\Handlers;

use App\AppointmentOrder;
use App\Libs\Push\Getui;
use App\User;
use App\UserGift;
use App\UserHongbao;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\Facades\JWTAuth;

class SwooleHandler
{
    private $user;

    private $redis;

    private $server;

    const IN = 1;
    const OUT = 0;

	public function __construct( $server )
	{
        $this->server = $server;
	}

    // WorkerStart回调
    public function onWorkerStart() {
        //redis连接
        $this->redis = Redis::connection('default');
    }

    public function onOpen(\swoole_websocket_server $server, $frame) {
    }

    //内部推送使用
    public function onRequest(\swoole_http_request $request, \swoole_http_response $response) {
        if ( $request->server['remote_addr'] == '127.0.0.1' ) {

            $data    = $request->post['data'];
            $user_id = $request->post['user_id'];

            $user_fd = Redis::get( 'lpsp_user_id_'.$user_id.'_fd.cache' );

            $ret = 0;
            if ( $user_fd ) {
                $ret = $this->push( $this->server, $user_fd, json_encode($data) );
            }

            $response->end( json_encode(['status' => $ret]) );
        }
    }

    public function onMessage(\swoole_websocket_server $server, $frame) {

        $data = json_decode( $frame->data, true );
        
        //heartbeat
        if ( $data['cmd'] === '0' ) {
            return true;
        }

        if ( !isset( $data['token'] ) ) {
            return false;
        }

        $this->user = JWTAuth::setToken( $data['token'] )->authenticate();

        Log::debug(' user_id=' . $this->user->id . ' cmd ' . $data['cmd'] );

        switch ( $data['cmd'] ) {
            case 'enter':
                $this->enterDaTing( $server, $data, $frame->fd );
                break;
            case 'next':
                $this->next( $server, $data, $frame->fd );
                break;
            case 'leave':
                $this->leave( $server, $data, $frame->fd );
                break;
            case 'updatefd':
                $this->updatefd( $server, $frame->fd );
                break;
            case 'gift':
                $this->gift( $server, $data, $frame->fd );
                break;
            case 'enterroom':
                $this->enterroom( $server, $data, $frame->fd );
                break;
            case 'outroom':
                $this->outroom( $server, $data, $frame->fd );
                break;
        }
    }

    //用户进入大厅
    public function enterDaTing( $server, $data, $fd )
    {
        //绑定user_id和fd
        $user_id = $this->user->id;
        
        $this->redis->set( 'lpsp_user_id_'.$user_id.'_fd.cache', $fd );
        $this->redis->set( 'lpsp_fd_id_'.$fd.'_user_id.cache', $user_id );

        //进入
        $this->updateUserDatingSet( $user_id, self::IN );

        //进入大厅后随机匹配一个用户
        $this->randOnlineUser( $server, $data, $fd );
    }

    //下一位
    public function next( $server, $data, $fd )
    {
        //退出房间
        $this->outroom( $server, $data, $fd );
        //回到大厅
        $this->reEnterDaTing( $server, $data, $fd );
        //随机匹配
        $this->randOnlineUser( $server, $data, $fd );
    }

    //随机分配一个用户
    public function randOnlineUser( $server, $data, $fd )
    {
        $user_id = $this->user->id;

        $lpsp_online_users_set = 'lpsp_online_users_set.cache';

        $online_count = $this->redis->scard( $lpsp_online_users_set );

        if ( $online_count == 1 ) {
            $return['cmd'] = 'n_u';
            $return['msg'] = 'no have user';
            $this->push($server, $fd, json_encode( $return ) );
            return false;
        }

        //随机一个用户
        $target_user_id = $this->redis->srandmember( $lpsp_online_users_set );

        if ( intval($target_user_id) === intval($user_id) ) {
            $this->randOnlineUser( $server, $data, $fd );
            return false;
        }

        $target_user = User::findOrFail( $target_user_id );

        $target_user_data = $target_user->toArray();
        unset( $target_user_data['rongyun_token'] );

        $chid = Uuid::uuid1();
        $target_user_fd = $this->redis->get( 'lpsp_user_id_'.$target_user_id.'_fd.cache' );
        list( $user_hongbao_id, $user_hongbao_code, $to_user_hongbao_id, $to_user_hongbao_code ) = $this->newHongbao( $user_id, $fd, $target_user_id, $target_user_fd );

        $return['cmd']          = 'match';
        $return['is_sender']    = 0;
        $return['user_info']    = $target_user_data;
        $return['chid']         = $chid;
        $return['hongbao']      = $user_hongbao_id;
        $return['to_hongbao_code']  = $to_user_hongbao_code;
        $return['fd']           = $fd;
        //推送给自己匹配到的人
        $user_push_ret = $this->push( $server, $fd, json_encode( $return ) );

        $user_data = $this->user->toArray();
        unset( $user_data['rongyun_token'] );

        $return['is_sender']    = 1;
        $return['user_info']    = $user_data;
        $return['chid']         = $chid;
        $return['hongbao']      = $to_user_hongbao_id;
        $return['to_hongbao_code'] = $user_hongbao_code;
        $return['fd']           = $target_user_fd;

        //推送给对方看是否要建立连接
        $target_push_ret = $this->push($server, $target_user_fd, json_encode( $return ) );

        if ( $user_push_ret && $target_push_ret ) {
            //更新所有在大厅的用户的用户列表
            $this->updateUserDatingSet( $user_id, self::OUT );
            $this->updateUserDatingSet( $target_user_id, self::OUT );
        }

        return true;
    }

    //重返大厅找人
    public function reEnterDaTing( $server, $data, $fd )
    {
        $user_id = $this->user->id;
        $this->updateUserDatingSet( $user_id, self::IN );
    }

    public function onClose($server, $fd) {
        try {
            $user_id = $this->redis->get('lpsp_fd_id_'.$fd.'_user_id.cache');

            //删除fd
            $this->redis->del( 'lpsp_fd_id_'.$fd.'_user_id.cache' );
            $this->redis->del( 'lpsp_user_id_'.$user_id.'_fd.cache' );

            //删除用户单次连接的历史next用户列表
            $this->redis->del( 'lpsp_online_next_history_' . $user_id . '.cache' );
            //下线
            $this->updateUserDatingSet( $user_id, self::OUT );

            //存在连接的对方用户回到在线列表
            // $target_user_id = $this->redis->get( 'lpsp_online_user_connect_'. $user_id .'.cache' );
            // $target_user_fd = $this->redis->get( 'lpsp_user_id_'.$target_user_id.'_fd.cache' );
            // if ( $target_user_id && $target_user_fd ) {
            //     $this->updateUserDatingSet( $target_user_id, self::IN );
            // }

            // $this->redis->del( 'lpsp_online_user_connect_'. $user_id .'.cache' );
            // $this->redis->del( 'lpsp_online_user_connect_'. $target_user_id .'.cache' );
            
        } catch (Exception $e) {
            Log::error('Message: ' .$e->getMessage());
        }
    }

    public function leave( $server, $data, $fd )
    {
        $user_id = $this->user->id;

        //离开房间，断开连接
        $this->reEnterDaTing( $server, $data, $fd );
        $this->onClose( $server, $fd );
    }

    //解决僵尸id问题
    //推送失败重试，3次重试还是失败的，清除相关在线记录及fd
    public function push( $server, $fd ,$data)
    {
        // Log::debug( 'push data ' . json_encode( $data ) );

        $ok = false;
        try{
            //用户不在线了，清除僵尸记录
            if ( !$server->connection_info( $fd ) ) {
                $this->onClose( $server, $fd );
                return $ok;
            }

            $ok = $server->push($fd, $data);
            
            //推送不能成功继续推送
            if ( !$ok ) {
                swoole_timer_tick( 1000, function( $id ) use ($server, $fd, $data) {
                    if ( !$server->connection_info( $fd ) ) {
                        $this->onClose( $server, $fd );
                        swoole_timer_clear( $id );
                        return;
                    }
                    $ok = $server->push($fd, $data);
                    if ( $ok ) {
                        swoole_timer_clear( $id );
                    }
                });
            }

            return true;
        } catch (Exception $e) {
           Log::error('Message: ' .$e->getMessage());
        }
        return $ok;
    }

    //更新用户句柄的关系
    public function updatefd( $server, $fd )
    {
        $user_id = $this->user->id;
        $this->redis->set( 'lpsp_user_id_'.$user_id.'_fd.cache', $fd );
        $this->redis->set( 'lpsp_fd_id_'.$fd.'_user_id.cache', $user_id );
    }

    //送礼物
    public function gift( $server, $data, $fd )
    {
        $user_id = $this->user->id;
        $user_gift_id = $data['id'];

        $user_gift = UserGift::findOrFail( $user_gift_id );

        $target_user_id = $user_gift->user_id;

        if ( $user_gift->state == 1 && $user_gift->from_uid == $user_id ) {

            $target_user_fd = $this->redis->get( 'lpsp_user_id_'.$target_user_id.'_fd.cache' );

            $data['receipt'] = $user_gift->receipt;

            //同时满足才推送
            $ret = $this->push($server, $target_user_fd, json_encode($data));
            if ( $ret ) {
                //已推送
                $user_gift->state = 2;
                $user_gift->save();
            }
        }
    }

    //生成红包
    private function newHongbao( $user_id, $user_fd, $target_user_id, $target_user_fd )
    {
        $date = date('Y-m-d');
        $time = time();
        $user_hongbao_id = $to_user_hongbao_id = 0;
        $user_hongbao_code = $to_user_hongbao_code = 0;

        $query = UserHongbao::where('status', 1)->where('create_date', $date);

        $user_today_hongbao = $query->where('user_id', $user_id)->get();
        $is_match_hongbao_get = 0;
        foreach ($user_today_hongbao as $item) {
            if ( $item->to_user_id == $target_user_id ) {
                $is_match_hongbao_get = 1;
                break;
            }
        }

        if( $user_today_hongbao->count() < 3 && !$is_match_hongbao_get ) {
            //成功连接创建红包
            $hongbao = new UserHongbao;
            $hongbao->user_id = $user_id;
            $hongbao->to_user_id = $target_user_id;
            $hongbao->value = rand( 1, 10 );
            $hongbao->code  = rand(1000, 9999);
            $hongbao->create_time = $time;
            $hongbao->create_date = $date;
            $hongbao->status = 0;
            $hongbao->fd = $user_fd;
            $hongbao->save();
            $user_hongbao_id   = $hongbao->id;
            $user_hongbao_code = $hongbao->code;
        }

        $query = UserHongbao::where('status', 1)->where('create_date', $date);
        $target_user_today_hongbao = $query->where('user_id', $target_user_id)->get();
        
        $target_is_match_hongbao_get = 0;
        foreach ($target_user_today_hongbao as $item) {
            if ( $item->to_user_id == $user_id ) {
                $target_is_match_hongbao_get = 1;
                break;
            }
        }

        if( $target_user_today_hongbao->count() < 3 && !$target_is_match_hongbao_get ) {
            $hongbao = new UserHongbao;
            $hongbao->user_id = $target_user_id;
            $hongbao->to_user_id = $user_id;
            $hongbao->value = rand( 1, 10 );
            $hongbao->code  = rand(1000, 9999);
            $hongbao->create_time = $time;
            $hongbao->create_date = $date;
            $hongbao->status = 0;
            $hongbao->fd = $target_user_fd;
            $hongbao->save();
            $to_user_hongbao_id   = $hongbao->id;
            $to_user_hongbao_code = $hongbao->code;
        }

        return [ $user_hongbao_id, $user_hongbao_code, $to_user_hongbao_id, $to_user_hongbao_code ];
    }

    //单独进入某个房间
    protected function enterroom( $server, $data, $fd )
    {
        $target_user = User::where('uuid', $data['uuid'])->first();
        if ( !$target_user ) {
            return false;
        }

        try {
            $notification = '您有新的视频聊天请求';
            $pushdata = [
                'type' => 2,
                'data' => [
                    'uuid' => $target_user->uuid
                ]
            ];
            Getui::serverPush( $target_user->cid, $notification, $notification, json_encode( $pushdata ), $pushdata );
        } catch (\Exception $e) {
        }

        $target_user_id = $target_user->id;

        //绑定user_id和fd
        $user_id = $this->user->id;
        
        $this->redis->set( 'lpsp_user_id_'.$user_id.'_fd.cache', $fd );
        $this->redis->set( 'lpsp_fd_id_'.$fd.'_user_id.cache', $user_id );
        
        $target_user_fd = $this->redis->get('lpsp_user_id_'.$target_user_id.'_fd.cache');
        if ( $target_user_fd ) {

            Log::debug ('user_id ' . $user_id . ' enterroom match target_user_id ' . $target_user_id );

            $chid = Uuid::uuid1();
            list( $user_hongbao_id, $user_hongbao_code, $to_user_hongbao_id, $to_user_hongbao_code ) = $this->newHongbao( $user_id, $fd, $target_user_id, $target_user_fd );

            $user_data = $target_user->toArray();
            unset( $user_data['rongyun_token'] );

            $return['cmd']          = 'match';
            $return['is_sender']    = 0;
            $return['user_info']    = $user_data;
            $return['chid']         = $chid;
            $return['hongbao']      = $user_hongbao_id;
            $return['to_hongbao_code']  = $to_user_hongbao_code;
            $return['fd']           = $fd;
            //推送给自己匹配到的人
            $user_push_ret = $this->push( $server, $fd, json_encode( $return ) );

            $user_data = $this->user->toArray();
            unset( $user_data['rongyun_token'] );

            $return['is_sender']    = 1;
            $return['user_info']    = $user_data;
            $return['chid']         = $chid;
            $return['hongbao']      = $to_user_hongbao_id;
            $return['to_hongbao_code'] = $user_hongbao_code;
            $return['fd']           = $target_user_fd;

            //推送给对方看是否要建立连接
            $target_push_ret = $this->push($server, $target_user_fd, json_encode( $return ) );

            if ( $user_push_ret && $target_push_ret ) {
                $this->updateUserDatingSet( $user_id, self::OUT );
                $this->updateUserDatingSet( $target_user_id, self::OUT );
            }
        } else {
            $return['cmd'] = 'n_u';
            $return['msg'] = 'no have user';
            $this->push($server, $fd, json_encode( $return ) );
            return false;
        }
    }

    //outroom 用户进入空闲状态
    protected function outroom( $server, $data, $fd )
    {
        $user_id = $this->user->id;

        $this->updateUserDatingSet( $user_id, self::OUT );

        if ( !isset( $data['uuid'] ) || empty($data['uuid'])  ) {
            return false;
        }

        $target_user = User::where('uuid', $data['uuid'])->first();
        if ( !$target_user ) {
            return false;
        }

        // Log::debug( 'outroom user_id '. $user_id .' target_user ' . $target_user->id  );

        $target_user_id = $target_user->id;
        //主动断开通知对方断开
        if ( $target_user_id ) {
            $this->updateUserDatingSet( $target_user_id, self::OUT );
            $target_user_fd = $this->redis->get( 'lpsp_user_id_'.$target_user_id.'_fd.cache' );
            //推送给对方,告诉对方是否连接或断开
            if ( $target_user_fd ) {
                $return['cmd'] = 'disconnect';
                $this->push($server, $target_user_fd, json_encode( $return ) );
            }
        }        
    }

    //更新用户池，后面添加多属性用户池用
    protected function updateUserDatingSet( $user_id, $status )
    {
        if( !$user_id ){
            return;
        }

        switch ( $status ) {
            case self::OUT:
                //用户离开大厅，或掉线，或已连接，或待机
                $this->redis->srem( 'lpsp_online_users_set.cache', $user_id );
                // $this->redis->srem( 'lpsp_online_users_sex_set_'. $user->sex .'.cache', $user_id );
                $this->redis->srem( 'lpsp_online_users', $user_id);
                break;
            case self::IN:
                //保存用户最后上线时间
                $this->redis->set('lpsp_online_users_set.online_time_' . $user_id, time());    
                //用户回到大厅
                $this->redis->sadd( 'lpsp_online_users_set.cache', $user_id );
                // $this->redis->sadd( 'lpsp_online_users_sex_set_'. $user->sex .'.cache', $user_id );
                $this->redis->sadd( 'lpsp_online_users', $user_id);
                break;
        }
    }

}