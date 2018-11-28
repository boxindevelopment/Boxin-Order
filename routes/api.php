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
    Route::prefix('product')->group(function() {
        Route::get('size/{types_of_box_room_id}', 'TypeSizeController@list')->name('api.size.list');
        Route::get('list/{area_id}', 'OrderController@chooseProduct')->name('api.order.chooseProduct');
        Route::get('check-available/{types_of_box_room_id}/area/{area_id}/size/{types_of_size_id}', 'OrderController@checkOrder')->name('api.order.checkOrder');
        Route::get('list-available/{types_of_box_room_id}/size/{types_of_size_id}', 'OrderController@listAvailable')->name('api.order.listAvailable');        
        Route::get('price/{types_of_box_room_id}/size/{types_of_size_id}/area/{area_id}', 'PriceController@listPriceArea')->name('api.price.getPriceArea');     
        Route::get('delivery-fee/{area_id}', 'DeliveryFeeController@deliveryFee')->name('api.delivery.deliveryFee');
    });

    Route::prefix('box')->group(function() {
        Route::get('list/{area_id}', 'BoxController@listByArea')->name('api.box.listByArea');
        Route::get('list-/{duration}', 'BoxController@getBox')->name('api.box.getBox');
    });

    Route::prefix('space')->group(function() {
        Route::get('list/{area_id}', 'SpaceController@listByArea')->name('api.space.listByArea');
    });

    Route::prefix('pickup')->group(function() {
        Route::get('type', 'TypePickupController@getType')->name('api.pickup.getType');
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


        Route::post('start-storing', 'OrderController@startStoring')->name('api.order.store')->middleware('auth:api');
        Route::get('find/{id}', 'OrderController@getOrder')->name('api.order.getOrder');
        Route::post('update', 'OrderController@update')->name('api.order.update');

        Route::post('start-item-box','OrderDetailBoxController@startDetailItemBox')->name('api.order.startDetailItemBox');
        Route::get('list-item-box/{order_detail_id}', 'OrderDetailBoxController@getItemByOrderDetailId')->name('api.order.getItemByOrderDetailId');
        Route::get('item-box/{id}', 'OrderDetailBoxController@getItemById')->name('api.order.getItemById');
        Route::post('item-box/update', 'OrderDetailBoxController@updateItem')->name('api.order.updateItem');
        Route::get('item-box/{id}/del', 'OrderDetailBoxController@destroy')->name('api.order.destroy');
        Route::post('item-box/deleteMultiple', 'OrderDetailBoxController@deleteMultiple')->name('api.order.deleteMultiple');
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


