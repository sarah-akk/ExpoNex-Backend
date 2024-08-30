<?php

use App\Http\Controllers\Api\V1\CategoryController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:api'], function () {

    Route::get('get', [CategoryController::class, 'Get']);
    Route::get('get-products/{category_id}/{exhibition_id}', [CategoryController::class, 'GetProducts']);

});