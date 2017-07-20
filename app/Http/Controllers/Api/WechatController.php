<?php

namespace App\Http\Controllers\Api;

use App\AppointmentOrder;
use App\Http\Controllers\Api\Third\RongyunController;
use App\Http\Controllers\Controller;
use App\Order;
use App\OrderItem;
use App\Product;
use App\User;
use App\UserAccount;
use App\UserCount;
use App\UserReceipt;
use App\WechatMenu;
use Carbon\Carbon;
use EasyWeChat\Message\Text;
use EasyWeChat\Payment\Order as WMOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\Facades\JWTAuth;

class WechatController extends Controller
{
    protected $wechat;

    protected $receipt_rmb_rate;
    protected $rmb_receipt_rate;

    private $repeat_count = 0;

    public function __construct()
    {
        $this->wechat = app('wechat');
        $this->receipt_rmb_rate = env( 'RECEIPT_TO_RMB_RATE' );
        $this->rmb_receipt_rate = env( 'RMB_TO_RECEIPT_RATE' );
    }

    public function index()
    {
        $server = $this->wechat->server;
        $server->setMessageHandler(function ($message) {
            Log::debug($message);
            $openid = $message->FromUserName;
            switch ($message->MsgType) {
                case 'event':
                    switch ($message->Event) {
                        case 'subscribe':
                            if ($user = User::where('openid', $openid)->first()) {
                            } else {
                                $wechat_user = $this->wechat->user->get($openid);
                                $unionid = $wechat_user['unionid'];
                                if ($user = User::where('unionid', $unionid)->first()) {
                                    $user->public_openid = $openid;
                                    $user->save();
                                } else {
                                    $user = new User;
                                    $user->uuid = Uuid::uuid1();
                                    $user->unionid = $unionid;
                                    $user->openid = '';
                                    $user->public_openid = $openid;
                                    $user->avatar = $wechat_user['headimgurl'];
                                    $user->nickname = $wechat_user['nickname'];
                                    $user->sex = $wechat_user['sex'];
                                    $user->country = $wechat_user['country'];
                                    $user->province = $wechat_user['province'];
                                    $user->city = $wechat_user['city'];
                                    $user->signature = '';
                                    $user->password = '';
                                    $user->save();

                                    $userCount = new UserCount();
                                    $user->count()->save($userCount);

                                    $userReceipt = new UserReceipt();
                                    $user->receipt()->save($userReceipt);

                                    app(RongyunController::class)->generate($user->id);
                                }
                            }

                            //关联处理
                            if ($message->EventKey && substr($message->EventKey, 0, 8) == 'qrscene_' && !$user->finder_id) {
                                $sceneId = intval(substr($message->EventKey, 8));
                                $user->finder_id = $sceneId;
                                $user->save();

                                $finder = User::where('id', $sceneId)->first();
                                $this->wechat->staff->message('恭喜。' . $user->nickname . '成为你的粉丝。' )->to($finder->public_openid)->send();
                            }

                            $this->wechat->staff->message('您好，欢迎关注轮盘视频！祝你玩得开心。')->to($openid)->send();
                            break;
                        case 'unsubscribe':
                            break;
                    }
                    break;
                case 'text':
                    break;
                default:
                    return "您好,欢迎关注!";
            }
        });
        $response = $server->serve();
        $response->send();
    }

    public function pay(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $productId = $request->input('product');
        $product = Product::findOrFail($productId);

        if($product->type == 2) {
            if ($product->value - $user->level > 1) {
                return response()->json(['code' => 2001, 'msg' => '无法跨等级购买']);
            }
        }

        $order = new Order();
        $order->trade_id = Carbon::now()->format('YmdHis') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $order->user_id = $user->id;
        $order->total = $product->price;
        $order->save();

        $orderItem = new OrderItem();
        $orderItem->product_id = $product->id;
        $orderItem->price = $product->price;
        $orderItem->quantity = 1;
        $order->items()->save($orderItem);

        $attributes = [
            'trade_type'       => 'JSAPI',
            'body'             => $product->name,
            'detail'           => $product->name,
            'out_trade_no'     => $order->trade_id,
            'total_fee'        => ($order->total*100),
            'openid'           => $user->public_openid,
        ];
        $order = new WMOrder($attributes);
        $data = $this->wechat->payment->prepare($order);
        if ($data->return_code == 'SUCCESS' && $data->result_code == 'SUCCESS'){
            $prepayId = $data->prepay_id;
            $data = $this->wechat->payment->configForPayment($prepayId);

            return response()->json(['code' => 1000, 'data' => $data]);
        }

        return response()->json(['code' => 9000, 'msg' => '创建订单失败']);

    }

    public function notify()
    {
        $response = $this->wechat->payment->handleNotify(function ($notify, $successful) {

            $order = Order::where('trade_id', $notify->out_trade_no)->where('status', 0)->first();
            if (!$order) {
                return true;
            }

            if ($successful) {
                $order->status = 1;
                $order->save();

                $user = $order->user;

                $appointmentOrder = AppointmentOrder::where('order_id', $order->id)->first();
                if ( $appointmentOrder ) {
                    $appointmentOrder->payed = 1;
                    $appointmentOrder->save();
                }

                //更新上级小票
                $this->updateFinderReceipt( $user->finder_id, $order->total );
                
                foreach ($order->items as $item) {
                    $product = $item->product;
                    switch ($product->type) {
                        case 1:
                            // $userCount = $user->count;
                            // $userCount->ZS += $product->value;
                            // $userCount->save();
                            UserAccount::in( UserAccount::BUSINESS_CHARGE, 0, $user->id, UserAccount::ZS, $product->value, '钻石充值');
                            break;
                        case 2:
                            $user->level = $product->value;
                            $user->save();
                            break;
                    }
                }
            }

            return true;
        });

        return $response;
    }

    public function menu()
    {
        $menu = $this->wechat->menu;

        $menu->destroy();

        $parameters = array(
            'view' => 'url'
        );
        $buttons = array();

        $wechatMenuGroup = WechatMenu::where('parent_id', 0)->get();
        foreach ($wechatMenuGroup as $group) {
            if ($group->children()->count()) {
                $subs = array();
                foreach ($group->children as $wechatMenu) {
                    $subs[] = array(
                        'type' => $wechatMenu->type,
                        'name' => $wechatMenu->name,
                        $parameters[$wechatMenu->type] => $wechatMenu->parameter
                    );
                }

                $buttons[] = array(
                    'name' => $group->name,
                    'sub_button' => $subs
                );
            } else {
                $buttons[] = array(
                    'type' => $group->type,
                    'name' => $group->name,
                    $parameters[$group->type] => $group->parameter
                );
            }
        }

        $response = $menu->add($buttons);

        return $response;
    }

    //更新上级小票
    private function updateFinderReceipt( $finder_id, $total )
    {
        if ( $this->repeat_count < 3 ) {
            $user = User::where('id', $finder_id)->first();

            if ( !$user ) {
                return;
            }

            //更新上级的小票,只发3级
            $finder_receipt = intval( $total * $this->rmb_receipt_rate * $this->finderLevel( $this->repeat_count ) );

            if ( $finder_receipt >= 1 ) {
                // $user_receipt = $user->receipt;
                // $user_receipt->receipt += $finder_receipt;
                // $user_receipt->save();
                UserAccount::in( UserAccount::BUSINESS_FINDER_GET, 0, $user->id, UserAccount::RECEIPT, $finder_receipt, '上级奖励小票');
                //推送
                $this->wechat->staff->message( '恭喜您获得' . $finder_receipt . '个小票的推荐奖励。' )->to($user->public_openid)->send();
            }

            $this->repeat_count++;

            $this->updateFinderReceipt( $user->finder_id, $total );
        }
    }

    private function finderLevel( $count )
    {
        if ( $count > 2 ) {
            return 0;
        }
        $rate = [
            0 => 0.1,
            1 => 0.05,
            2 => 0.05
        ];

        return $rate[ $count ];
    }
}
