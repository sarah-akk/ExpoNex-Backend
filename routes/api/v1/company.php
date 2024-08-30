<?php

use App\Http\Controllers\Api\V1\CompanyController;
use Illuminate\Support\Facades\Route;



Route::group(['middleware' => 'auth:api'], function () {

    Route::put('/create', [CompanyController::class, 'Create']);
    Route::get('/get', [CompanyController::class, 'GetCompanies']);
    Route::get('/get/{company_id}', [CompanyController::class, 'GetCompany']);

});



