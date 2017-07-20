<?php

namespace App\Http\Controllers\Api\Third;

use App\Http\Controllers\Controller;
use App\Order;
use App\OrderApi;
use App\User;
use App\UserAccount;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class AppleController extends Controller
{
    const SANDBOXAPIURL = 'https://sandbox.itunes.apple.com/verifyReceipt';
    const PRODUCTIONAPIURL = 'https://buy.itunes.apple.com/verifyReceipt';
    const PRODUCTPREFIX = 'com.tcpan.lpspvideo.';

    protected $receipt_rmb_rate;
    protected $rmb_receipt_rate;
    protected $repeat_count = 0;

    public function __construct()
    {
        $this->receipt_rmb_rate = env( 'RECEIPT_TO_RMB_RATE' );
        $this->rmb_receipt_rate = env( 'RMB_TO_RECEIPT_RATE' );
    }

    public function verify(Request $request)
    {
        $receipt = $request->input('receipt');
        $orderId = $request->input('order');

        $params = ['receipt-data' => $receipt];
        $body = json_encode($params);

        $client = new Client;
        $response = $client->request('POST', self::SANDBOXAPIURL, [
            'body' => $body
        ]);

        $body = $response->getBody();
        $data = json_decode($body, true);

        //苹果返回成功
        if ($data['status'] == 0) {
            //订单未存在处理记录
            if (!OrderApi::where('trade_id', $data['receipt']['transaction_id'])->exists()) {
                //商品ID符合规范
                if ($productId = intval(substr($data['receipt']['product_id'], strlen(self::PRODUCTPREFIX)))) {
                    //订单有效
                    if ($order = Order::where('trade_id', $orderId)->where('status', 0)->first()) {
                        $orderItem = $order->items->first();
                        $product = $orderItem->product;
                        //支付的商品ID和传输的商品ID相同（防止伪造订单ID）
                        if ($productId == $product->id) {
                            $order->status = 1;
                            $order->save();

                            $orderApi = new OrderApi;
                            $orderApi->order_id = $order->id;
                            $orderApi->trade_id = $data['receipt']['transaction_id'];
                            $orderApi->type = 3;
                            $orderApi->response_body = $body;
                            $orderApi->save();

                            $user = $order->user;

                            //更新上级小票
                            $this->updateFinderReceipt( $user->finder_id, $order->total );

                            foreach ($order->items as $item) {
                                $product = $item->product;
                                switch ($product->type) {
                                    case 1:
                                        // $userCount = $user->count;
                                        // $userCount->ZS += $product->value;
                                        // $userCount->save();
                                        UserAccount::in( UserAccount::BUSINESS_CHARGE, 0, $user->id, UserAccount::ZS, $product->value, '用户充值');
                                        break;
                                }
                            }

                            return response()->json(['code' => 1000]);
                        }
                    }
                }
            }

            return response()->json(['code' => 1001]);
        }

        return response()->json(['code' => 2000]);
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
