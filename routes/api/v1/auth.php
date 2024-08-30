<?php

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController as AuthC;

//Route::post('/login', [AuthC::class, 'login']);
Route::post('/user-verification', [AuthC::class, 'UserVerification']);
Route::post('/resend-verification-code', [AuthC::class, 'ReSendVerificationCode']);
Route::post('/refresh-token', [AuthC::class, 'Refresh']);

Route::post('/forget-password', [AuthC::class, 'ForgetPassword']);
Route::post('/resend-recovery-code', [AuthC::class, 'ResendForgetPasswordCode']);
Route::post('/user-recovery', [AuthC::class, 'UserRecovery']);
Route::post('/change-password', [AuthC::class, 'ChangePassword']);


Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/logout', [AuthC::class, 'Logout']);
});



// Route::group([
//     'as' => 'passport.',
//     'namespace' => '\Laravel\Passport\Http\Controllers',
//     'middleware' => 'server-request'
// ], function () {
//     Route::post('/token', [
//         'uses' => 'AccessTokenController@issueToken',
//         'as' => 'token',
//         'middleware' => 'throttle',
//     ]);
// });
