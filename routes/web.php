<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('download', function () {
    return view('download');
});

Route::group(['middleware' => ['wechat.oauth', 'auth']], function () {

    Route::get('account/charge', 'AccountController@charge');
    Route::get('account/vip', 'AccountController@vip');
    Route::get('account/qrcode', 'AccountController@qrcode');

});

Route::group(['namespace'=>'admin','prefix'=>'admin'],function (){
//    Route::get('/','MediaController@index');
//    Route::get('media','MediaController@mediaIndex');
//    Route::post('media/post','MediaController@postMedia');
//    Route::get('users/search','MediaController@searchUser');
//    Route::post('resource/store','MediaController@storeMediaResource');

    Route::get('/','UserController@index');
    Route::get('/{id}/edit','UserController@edit');
    Route::post('upload','UserController@upload');
    Route::get('/users/search','UserController@search');
    Route::post('/store','UserController@store');

    Route::resource('/order','OrderController');
    Route::get('order/consume','OrderController@details');

    Route::get('{id}/consume','UserController@details');
});
