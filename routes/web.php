<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('payment')->group(function() {
    Route::get('finish', 'Api/PaymentController@showFinish')->name('pay.finish');
    Route::get('unfinish', 'Api/PaymentController@showUnfinish')->name('pay.unfinish');
    Route::get('error', 'Api/PaymentController@showError')->name('pay.error');
});
