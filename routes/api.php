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
    });

    Route::prefix('room')->group(function() {
        Route::get('list-room/{space_id}', 'RoomController@getRoomBySpace')->name('api.room.getRoomBySpace');
    });

    Route::prefix('pickup')->group(function() {
        Route::get('type', 'PickupOrderController@getType')->name('api.pickup.getType');
        Route::post('start-pickup', 'PickupOrderController@startPickUp')->name('api.pickup.startPickUp');
    });

    Route::prefix('order')->group(function() {
        Route::get('my-box/{user_id}', 'OrderController@my_box')->name('api.order.my_box');
        Route::get('my-deliveries/{user_id}', 'OrderController@my_deliveries')->name('api.order.my_deliveries');
        Route::get('my-box-history/{user_id}', 'OrderController@my_box_history')->name('api.order.my_box_history');

        Route::get('price/{types_of_box_room_id}/size/{types_of_size_id}', 'OrderController@getPrice')->name('api.order.getPrice');

        Route::post('start-storing', 'OrderController@startStoring')->name('api.order.store');
        Route::get('{order_detail_id}', 'OrderController@getById')->name('api.order.getById');
        Route::post('update', 'OrderController@update')->name('api.order.update');

        Route::post('start-item-box','OrderDetailBoxController@startDetailItemBox')->name('api.order.startDetailItemBox');
        Route::get('list-item-box/{order_detail_id}', 'OrderDetailBoxController@getItemByOrderDetailId')->name('api.order.getItemByOrderDetailId');
        Route::get('item-box/{item_box_id}', 'OrderDetailBoxController@getItemById')->name('api.order.getItemById');
        Route::post('item-box/update', 'OrderDetailBoxController@updateItem')->name('api.order.updateItem');
        Route::delete('item-box/{item_box_id}', 'OrderDetailBoxController@deleteItem')->name('api.order.deleteItem');
    });

    Route::prefix('payment')->group(function() {
        Route::post('start-payment', 'PaymentController@startPayment')->name('api.payment.startPayment');
    });
});