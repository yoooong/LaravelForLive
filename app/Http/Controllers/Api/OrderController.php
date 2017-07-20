<?php

namespace App\Http\Controllers\Api;

use App\Order;
use App\OrderItem;
use App\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderController extends Controller
{
    public function create(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $productId = $request->input('product');
        $product = Product::findOrFail($productId);

        $order = new Order;
        $order->trade_id = Carbon::now()->format('YmdHis') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $order->user_id = $user->id;
        $order->total = $product->price;
        $order->save();

        $orderItem = new OrderItem;
        $orderItem->product_id = $product->id;
        $orderItem->price = $product->price;
        $orderItem->quantity = 1;
        $order->items()->save($orderItem);

        return response()->json(['code' => 1000, 'data' => ['id' => $order->trade_id, 'total' => $order->total]]);
    }
}
