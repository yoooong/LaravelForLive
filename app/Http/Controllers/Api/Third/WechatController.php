<?php

namespace App\Http\Controllers\Api\Third;

use App\AppointmentOrder;
use App\Http\Controllers\Controller;
use App\Libs\Push\AppointmentPush;
use App\Order;
use App\OrderItem;
use App\Product;
use App\User;
use App\UserAccount;
use App\UserChange;
use App\UserExchange;
use Carbon\Carbon;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order as WMOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class WechatController extends Controller
{
    private $wechat;
    protected $wechat_receipt;
    protected $wechat_payment;
    protected $wechat_app;

    public function __construct()
    {
        $this->wechat = app('wechat');

        // $this->wechat_receipt = new Application([
        //     'app_id' => env('RECEIPT_WECHAT_APPID'),
        //     'secret' => env('RECEIPT_WECHAT_SECRET'),
        //     'token' => env('RECEIPT_WECHAT_TOKEN'),
        //     'aes_key' => env('RECEIPT_WECHAT_AES_KEY'),
        //     'payment' => [
        //         'merchant_id' => env('RECEIPT_WECHAT_PAYMENT_MERCHANT_ID'),
        //         'key' => env('RECEIPT_WECHAT_PAYMENT_KEY'),
        //         'cert_path' => env('RECEIPT_WECHAT_PAYMENT_CERT_PATH'),
        //         'key_path' => env('RECEIPT_WECHAT_PAYMENT_KEY_PATH'),
        //         'notify_url' => env('RECEIPT_WECHAT_PAYMENT_NOTIFY_URL'),
        //     ],
        // ]);
        // $this->wechat_payment = new Application([
        //     'app_id' => env('PAYMENT_WECHAT_APPID'),
        //     'secret' => env('PAYMENT_WECHAT_SECRET'),
        //     'token' => env('PAYMENT_WECHAT_TOKEN'),
        //     'aes_key' => env('PAYMENT_WECHAT_AES_KEY'),
        //     'payment' => [
        //         'merchant_id' => env('PAYMENT_WECHAT_PAYMENT_MERCHANT_ID'),
        //         'key' => env('PAYMENT_WECHAT_PAYMENT_KEY'),
        //         'cert_path' => env('PAYMENT_WECHAT_PAYMENT_CERT_PATH'),
        //         'key_path' => env('PAYMENT_WECHAT_PAYMENT_KEY_PATH'),
        //         'notify_url' => env('PAYMENT_WECHAT_PAYMENT_NOTIFY_URL'),
        //     ],
        // ]);
        $this->wechat_app = new Application([
            'app_id'  => env('APP_WECHAT_APPID'),
            'secret'  => env('APP_WECHAT_SECRET'),
            'token'   => env('APP_WECHAT_TOKEN'),
            'aes_key' => env('APP_WECHAT_AES_KEY'),
            'payment' => [
                'merchant_id' => env('APP_WECHAT_PAYMENT_MERCHANT_ID'),
                'key' => env('APP_WECHAT_PAYMENT_KEY'),
                'cert_path' => env('APP_WECHAT_PAYMENT_CERT_PATH'),
                'key_path' => env('APP_WECHAT_PAYMENT_KEY_PATH'),
                'notify_url' => env('APP_WECHAT_PAYMENT_NOTIFY_URL'),
            ],
        ]);
    }

    //发红包
    public function sendLuckyMoney($user_id, $mch_billno, $rmb)
    {
        $luckyMoney = $this->wechat->lucky_money;

        $user = User::findOrFail($user_id);

        $userChange = new UserChange;
        $userChange->trade_id = Carbon::now()->format('YmdHis') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $userChange->user_id = $user->id;
        $userChange->key = $user->id . '-' . Carbon::now()->format('YmdHi');
        $userChange->rmb = $rmb;
        $userChange->save();

        $luckyMoneyData = [
            'mch_billno' => $mch_billno,
            'send_name' => '兑换红包',
            're_openid' => $user->public_openid,
            'total_num' => 1,  //固定为1，可不传
            'total_amount' => $rmb * 100,  //单位为分，不小于300
            'wishing' => '兑换红包',
            'act_name' => '轮盘视频',
            'remark' => '轮盘视频',
        ];
        $result = $luckyMoney->sendNormal($luckyMoneyData);

        Log::debug('WechatController sendLuckyMoney ' . $result);

        return $result;
    }

    public function prepare(Request $request, $isApp = 0)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $productId = $request->input('product');
        $product = Product::findOrFail($productId);

        if ($product->type == 2) {
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
            'trade_type' => 'APP',
            'body' => $product->name,
            'detail' => $product->name,
            'out_trade_no' => $order->trade_id,
            'total_fee' => ($order->total * 100),
            'openid' => $user->openid,
        ];
        $order = new WMOrder($attributes);
        $data = $this->wechat_app->payment->prepare($order);

        if ($data->return_code == 'SUCCESS' && $data->result_code == 'SUCCESS') {
            $prepayId = $data->prepay_id;
            if ( $isApp ) {
                $data = $this->wechat_app->payment->configForAppPayment($prepayId);
            } else {
                $data = $this->wechat_app->payment->configForPayment($prepayId);    
            }

            return response()->json(['code' => 1000, 'data' => $data]);
        }

        return response()->json(['code' => 9000, 'msg' => '创建订单失败']);
    }

    public function prepareApp( Request $request )
    {
        switch ( $request->input('type') ) {
            case 1:
            case 2:
                //购买VIP,钻石
                return $this->prepare( $request, 1 );
            case 3:
                //约聊
                return $this->prepareAppointment( $request );
        }
    }

    public function prepareAppointment(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $id = $request->input('appointment');

        $appointmentOrder = AppointmentOrder::findOrFail( $id );
        if ( $appointmentOrder->user_id != $user->id ) {
            return response()->json(['code' => 1001, 'msg' => '下单用户不匹配']);
        }

        if ( $appointmentOrder->status != 0 ) {
            return response()->json(['code' => 1002, 'msg' => '已支付过该订单']);
        }

        $productId = 12;
        $product = Product::findOrFail($productId);

        $order = new Order;
        $order->trade_id = Carbon::now()->format('YmdHis') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $order->user_id = $user->id;
        $order->total = $appointmentOrder->price ;
        $order->save();

        $orderItem = new OrderItem;
        $orderItem->product_id = $product->id;
        $orderItem->price = $appointmentOrder->price;
        $orderItem->quantity = $appointmentOrder->num;
        $order->items()->save($orderItem);

        $appointmentOrder->order_id = $order->id;
        $appointmentOrder->save();

        $attributes = [
            'trade_type' => 'APP',
            'body' => $product->name,
            'detail' => $product->name,
            'out_trade_no' => $order->trade_id,
            'total_fee' => ($order->total * 100),
            'openid' => $user->openid,
        ];
        $order = new WMOrder($attributes);
        $data = $this->wechat_app->payment->prepare($order);

        if ($data->return_code == 'SUCCESS' && $data->result_code == 'SUCCESS') {
            $prepayId = $data->prepay_id;
            $data = $this->wechat_app->payment->configForAppPayment($prepayId);

            return response()->json(['code' => 1000, 'data' => $data]);
        }

        return response()->json(['code' => 9000, 'msg' => '创建订单失败']);
    }

    public function notifyApp()
    {
        $response = $this->wechat_app->payment->handleNotify(function ($notify, $successful) {

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
                    $appointmentOrder->payed_time = time();
                    $appointmentOrder->payed = 1;
                    $appointmentOrder->save();
                    
                    try {
                        $data = [
                            'cmd'       => 'appointment',
                            'id'        => $appointmentOrder->id,
                            'nickname'  => $user->nickname,
                            'avatar'    => $user->avatar,
                            'payed_time' => $appointmentOrder->payed_time
                        ];
                        //socket推送
                        AppointmentPush::job( $appointmentOrder->target_user_id, $data );   
                    } catch (\Exception $e) {
                        
                    }
                }

               // //更新上级小票
               // $this->updateFinderReceipt( $user->finder_id, $order->total );

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
                    $appointmentOrder->payed_time = time();
                    $appointmentOrder->payed = 1;
                    $appointmentOrder->save();
                    
                    try {
                        $data = [
                            'cmd'       => 'appointment',
                            'id'        => $appointmentOrder->id,
                            'nickname'  => $user->nickname,
                            'avatar'    => $user->avatar,
                            'payed_time' => $appointmentOrder->payed_time
                        ];
                        //socket推送
                        AppointmentPush::job( $appointmentOrder->target_user_id, $data );   
                    } catch (\Exception $e) {
                        
                    }
                }

//                //更新上级小票
//                $this->updateFinderReceipt( $user->finder_id, $order->total );

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
}