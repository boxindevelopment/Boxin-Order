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
    Route::prefix('box')->group(function() {
        Route::get('list-box/{space_id}', 'BoxController@getBoxBySpace')->name('api.box.getBoxBySpace');
        Route::get('list/{duration}', 'BoxController@getBox')->name('api.box.getBox');
    });

    Route::prefix('room')->group(function() {
        Route::get('list-room/{space_id}', 'RoomController@getRoomBySpace')->name('api.room.getRoomBySpace');
    });

    Route::prefix('pickup')->group(function() {
        Route::get('price', 'PickupOrderController@getPrice')->name('api.pickup.getPrice');
        Route::get('type', 'PickupOrderController@getType')->name('api.pickup.getType');
        Route::post('start-pickup', 'PickupOrderController@startPickUp')->name('api.pickup.startPickUp');
    });

    Route::prefix('return')->group(function() {
        Route::get('price', 'ReturnBoxController@getPrice')->name('api.return.getPrice');
        Route::post('start-return', 'ReturnBoxController@startReturnBox')->name('api.return.startReturnBox')->middleware('auth:api');
        Route::get('my-deliveries', 'ReturnBoxController@my_deliveries')->name('api.order.my_deliveries')->middleware('auth:api');
    });

    Route::prefix('order')->group(function() {
        Route::get('my-box', 'OrderDetailController@my_box')->name('api.order.my_box')->middleware('auth:api');
        Route::get('my-box-history', 'OrderDetailController@my_box_history')->name('api.order.my_box_history')->middleware('auth:api');        
        Route::get('{order_detail_id}', 'OrderDetailController@getById')->name('api.order.getById')->middleware('auth:api');

        Route::get('product/list-choose', 'OrderController@chooseProduct')->name('api.order.chooseProduct');
        Route::get('price/{types_of_box_room_id}/size/{types_of_size_id}', 'OrderController@getPrice')->name('api.order.getPrice');

        Route::post('start-storing', 'OrderController@startStoring')->name('api.order.store')->middleware('auth:api');
        Route::get('find/{id}', 'OrderController@getOrder')->name('api.order.getOrder');
        Route::post('update', 'OrderController@update')->name('api.order.update');

        Route::post('start-item-box','OrderDetailBoxController@startDetailItemBox')->name('api.order.startDetailItemBox');
        Route::get('list-item-box/{order_detail_id}', 'OrderDetailBoxController@getItemByOrderDetailId')->name('api.order.getItemByOrderDetailId');
        Route::get('item-box/{id}', 'OrderDetailBoxController@getItemById')->name('api.order.getItemById');
        Route::post('item-box/update', 'OrderDetailBoxController@updateItem')->name('api.order.updateItem');
        Route::get('item-box/{id}/del', 'OrderDetailBoxController@destroy')->name('api.order.destroy');
    });

    Route::prefix('payment')->group(function() {
        Route::post('start-payment', 'PaymentController@startPayment')->name('api.payment.startPayment')->middleware('auth:api');
    });

    // midtrans
    // Route::get('/vtweb', 'PagesController@vtweb');

    Route::get('/vtdirect', 'VtdirectController@vtdirect');
    Route::post('/vtdirect', 'VtdirectController@checkout_process');

    Route::get('/vt_transaction', 'TransactionController@transaction');
    Route::post('/vt_transaction', 'TransactionController@transaction_process');

    Route::post('/vt_notif', 'SnapController@notification');

    Route::get('/snap', 'SnapController@snap');
    Route::get('/snaptoken', 'SnapController@token');
    Route::post('/snapfinish', 'SnapController@finish');

});


