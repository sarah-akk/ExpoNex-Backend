<?php

use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::put('/create', [UserController::class, 'Create']);

Route::group(['middleware' => 'auth:api'], function () {

    Route::patch('/update', [UserController::class, 'Update']);
    Route::patch('/change-phonenumber', [UserController::class, 'ChangePhoneNumber']);
    Route::post('/phone-verification', [UserController::class, 'PhoneVerification']);


    //Admins

});
