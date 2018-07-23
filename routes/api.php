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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'Api'], function() {
    Route::prefix('order')->group(function() {
        Route::get('', 'OrderController@index')->name('api.order');
        Route::post('start-storing', 'OrderController@startStoring')->name('api.order.store');
        Route::post('pick-box/{order}', 'OrderController@pickBox')->name('api.order.box');
        Route::post('set-duration/{order}', 'OrderController@setDuration')->name('api.order.duration');
    });
});