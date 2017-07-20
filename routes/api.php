<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' => 'Api'], function () {

    Route::get('test/testredis', 'TestController@testredis');
    Route::get('test/user', 'TestController@info');
    Route::get('test/tim', 'TestController@testtim');
    Route::get('test/testhongbao', 'TestController@testhongbao');
    Route::post('test/serverpush', 'TestController@serverPush');
    
    Route::get('sys/conf', 'SysController@conf');

    Route::any('wechat', 'WechatController@index');
    Route::post('wechat/menu', 'WechatController@menu');

    Route::get('qrcode/create', 'QrcodeController@create');
    Route::get('version/check', 'VersionController@check');
    Route::get('auth/user', 'AuthController@user');
    Route::get('auth/wechat', 'AuthController@wechat');
    Route::post('auth/mobile', 'AuthController@mobile');
    Route::get('auth/sendcode', 'AuthController@sendcode');
    Route::get('gift/list', 'GiftController@index');
    Route::get('vip/list', 'VipController@all');
    Route::post('wechat/notify', 'WechatController@notify');
    Route::get('product/list', 'ProductController@index');

    Route::group(['namespace' => 'Third', 'prefix' => 'third'], function () {
        Route::post('apple/verify', 'AppleController@verify');
        Route::post('alipay/notify', 'AlipayController@notify');
        Route::post('wechat/notify', 'WechatController@notify');
        Route::post('wechat/notify_app', 'WechatController@notifyApp');
        Route::group(['middleware' => ['jwt.auth']], function () {
            Route::post('alipay/purchase', 'AlipayController@purchase');
            Route::post('wechat/prepare', 'WechatController@prepare');

            Route::post('alipay/purchase_appointment', 'AlipayController@purchaseAppointment');
            Route::post('wechat/prepare_appointment', 'WechatController@prepareAppointment');

            Route::post('alipay/purchase_app', 'AlipayController@purchaseApp');
            Route::post('wechat/prepare_app', 'WechatController@prepareApp');

            Route::post('getui/bind', 'GetuiController@bind');
            Route::post('getui/unbind', 'GetuiController@unbind');
        });
    });

    Route::group(['middleware' => ['jwt.auth']], function () {
        Route::post('wechat/pay', 'WechatController@pay');

        Route::get('user/info', 'UserController@info');
        Route::get('user/count', 'UserController@count');
        Route::post('user/update', 'UserController@update');
        Route::get('user/center', 'UserController@center');
        Route::get('user/timsig', 'UserController@timsig');
        Route::post('user/bindmobile', 'UserController@bindmobile');
        Route::post('user/bindwechat', 'UserController@bindwechat');

        Route::post('fan/query', 'FanController@query');
        Route::post('fan/idols', 'FanController@idols');
        Route::post('fan/remember', 'FanController@remember');
        Route::post('order/create', 'OrderController@create');

        Route::any('gift/send', 'GiftController@send');

        Route::get('blacklist/list', 'BlacklistController@all');
        Route::post('blacklist/add', 'BlacklistController@add');

        Route::get('receipt/count', 'ReceiptController@count');
        Route::post('receipt/exchange', 'ReceiptController@exchange');
        Route::get('receipt/list', 'ReceiptController@zslist');

        Route::get('exchange/query', 'ExchangeController@query');
        Route::get('exchange/querygetrmb', 'ExchangeController@queryGetRmb');

        Route::post('exchange/rmb', 'ExchangeController@rmb');
        Route::post('exchange/send', 'ExchangeController@send');

        Route::post('complain/add', 'ComplainController@add');

        Route::get('online/users', 'OnlineController@users');
        Route::post('online/score', 'OnlineController@scoreList');
        Route::get('online/tag', 'OnlineController@tag');

        Route::get('appointment/jobs', 'AppointmentController@jobs');
        Route::get('appointment/detail', 'AppointmentController@detail');
        Route::post('appointment/create', 'AppointmentController@create');
        Route::post('appointment/comfirm', 'AppointmentController@comfirm');
        Route::get('appointment/order', 'AppointmentController@order');
        Route::get('appointment/end', 'AppointmentController@end');
        Route::post('appointment/score', 'AppointmentController@score');

        Route::post('hongbao/code', 'HongbaoController@code');
        Route::post('hongbao/get', 'HongbaoController@get');
        Route::post('hongbao/unlock','HongbaoController@shareUnlock');

        Route::post('search/user', 'SearchController@user');
    });
});