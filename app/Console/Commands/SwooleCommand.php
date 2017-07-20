<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class SwooleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole {action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'swooler socket';

    private $daemonize;
    private $port;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->daemonize = env('SWOOLE_SOCKET_DAEMONIZE');
        $this->port = env('SWOOLE_SOCKET_PORT');
        parent::__construct();
    }

    private function start()
    {
        $server = new \swoole_websocket_server("0.0.0.0", $this->port);
        // 设置参数
        $server->set(array(
            'worker_num'               => 8,
            'max_request'              => 100000,
            'daemonize'                => $this->daemonize,
            'heartbeat_check_interval' => 10,
            'heartbeat_idle_time'      => 10,
            'dispatch_mode'            => 2,
            'user'                     => 'nginx',
            'group'                    => 'nginx',
            // 'open_length_check'        => true,
            // 'package_length_type'      => 'N',
            'package_length_offset'    => 0,
            'package_max_length'       => 800000,
        ));
        
        $handler = App::make('App\Handlers\SwooleHandler', ['server' => $server]);

        // 绑定WorkerStart
        $server->on('workerstart' , array( $handler , 'onWorkerStart'));
        // 绑定request
        $server->on('open', array( $handler, 'onOpen'));
        // 监听消息
        $server->on('message', array( $handler, 'onMessage'));
        // 监听http请求
        $server->on('request', array( $handler, 'onRequest'));
        // 监听关闭
        $server->on('close', array( $handler, 'onClose' ));

        $server->start();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $arg = $this->argument('action');
        switch ( $arg ) {
            case 'start':
                $this->info('swoole socket started');
                $this->start();
                break;
            case 'stop':
                $this->info('swoole socket can not stop');
                break;

            case 'reload':
                $this->info('swoole socket reload now');
                break;
        }
    }
}
