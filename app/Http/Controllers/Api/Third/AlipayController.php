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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Omnipay\Omnipay;
use Tymon\JWTAuth\Facades\JWTAuth;

class AlipayController extends Controller
{
    protected $gateway;

    protected $receipt_rmb_rate;
    protected $rmb_receipt_rate;
    protected $repeat_count = 0;

    public function __construct()
    {
        $appId = env('ALIPAY_APP_ID');
        $privateKey = env('ALIPAY_PRIVATE_KEY');
        $publicKey = env('ALIPAY_PUBLIC_KEY');
        $notifyUrl = env('ALIPAY_NOTIFY_URL');

        $this->gateway = Omnipay::create('Alipay_AopApp');
        $this->gateway->setAppId($appId);
        $this->gateway->setPrivateKey($privateKey);
        $this->gateway->setAlipayPublicKey($publicKey);
        $this->gateway->setNotifyUrl($notifyUrl);

        $this->receipt_rmb_rate = env( 'RECEIPT_TO_RMB_RATE' );
        $this->rmb_receipt_rate = env( 'RMB_TO_RECEIPT_RATE' );
    }

    public function purchase(Request $request)
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

        $gateway = $this->gateway->purchase();
        $gateway->setBizContent([
            'subject' => $order->items->first()->product->name,
            'out_trade_no' => $order->trade_id,
            'total_amount' => $order->total,
            'product_code' => 'QUICK_MSECURITY_PAY',
        ]);

        $response = $gateway->send();

        return response()->json(['code' => 1000, 'data' => ['order' => $response->getOrderString()]]);
    }

    public function purchaseApp( Request $request )
    {
        switch ( $request->input('type') ) {
            case 1:
            case 2:
                //购买VIP,钻石
                return $this->purchase( $request );
            case 3:
                //约聊
                return $this->purchaseAppointment( $request );
        }
    }

    public function purchaseAppointment( Request $request )
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
        $order->total = $appointmentOrder->price;
        $order->save();

        $orderItem = new OrderItem;
        $orderItem->product_id = $product->id;
        $orderItem->price = $appointmentOrder->price;
        $orderItem->quantity = $appointmentOrder->num;
        $order->items()->save($orderItem);

        $appointmentOrder->order_id = $order->id;
        $appointmentOrder->save();

        $gateway = $this->gateway->purchase();
        $gateway->setBizContent([
            'subject' => $product->name,
            'out_trade_no' => $order->trade_id,
            'total_amount' => $order->total,
            'product_code' => 'QUICK_MSECURITY_PAY',
        ]);

        $response = $gateway->send();

        return response()->json(['code' => 1000, 'data' => ['order' => $response->getOrderString()]]);
    }

    public function notify(Request $request)
    {
        $gateway = $this->gateway->completePurchase();
        $gateway->setParams($_POST);

        Log::debug($request->all());

        try {
            $response = $gateway->send();

            Log::debug($response->getData());

            if($response->isPaid()){
                $orderId = $response->data('out_trade_no');
                if ($order = Order::where('trade_id', $orderId)->where('status', 0)->first()) {
                    $order->status = 1;
                    $order->save();

                    $user = $order->user;

                    $appointmentOrder = AppointmentOrder::where('order_id', $order->id)->first();
                    if ( $appointmentOrder ) {
                        $appointmentOrder->payed_time = time();
                        $appointmentOrder->payed = 1;
                        $appointmentOrder->save();
                        $data = [
                            'cmd'       => 'appointment',
                            'id'        => $appointmentOrder->id,
                            'nickname'  => $user->nickname,
                            'avatar'    => $user->avatar,
                            'payed_time' => $appointmentOrder->payed_time
                        ];
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
                exit('success');
            }else{
                exit('fail');
            }
        } catch (Exception $e) {

            exit('fail');
        }
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
