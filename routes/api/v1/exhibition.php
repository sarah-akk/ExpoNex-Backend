<?php

use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\ExhibitionController;
use App\Http\Controllers\Api\V1\TicketController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:api'], function () {

    Route::get('get', [ExhibitionController::class, 'GetExhibitions']);
    Route::get('get/{exhibition_id}', [ExhibitionController::class, 'GetExhibition']);

    Route::get('get/{exhibition_id}/ticket', [TicketController::class, 'GetTicket']);
});
