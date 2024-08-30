<?php
use App\Http\Controllers\Api\V1\TicketController;
use App\Http\Controllers\Api\V1\WalletController;
use App\Http\Controllers\Api\V1\OrderController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:api'], function () {

    Route::get('wallet/get', [WalletController::class, 'Get']);
    Route::post('wallet/add-balance', [WalletController::class, 'AddBalance']);

    Route::get('order/get', [OrderController::class, 'GetMyOrder']);

    Route::put('order/ticket/create', [TicketController::class, 'Buy']);
    Route::put('order/product/create', [OrderController::class, 'Create']);
    Route::patch('order/product/cancel', [OrderController::class, 'Cancel']);
});