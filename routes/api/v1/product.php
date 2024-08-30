<?php
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;


Route::group(['middleware' => 'auth:api'], function () {
    Route::get('get/{exhibition_id}', [ProductController::class, 'GetProducts']);
    Route::get('get/{exhibition_id}/{product_id}', [ProductController::class, 'GetProduct']);
});