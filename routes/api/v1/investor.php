<?php

use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\ExhibitionController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => [
        'auth:api',
        'check_exhibition_onwer',
    ]
], function () {

    Route::get('exhibition/get', [ExhibitionController::class, 'GetMyExpos']);
    Route::get('exhibition/get/{exhibition_id}', [ExhibitionController::class, 'GetMyExpo']);
    Route::get('exhibition/get/{exhibition_id}/products-number', [ExhibitionController::class, 'GetProductsNumber']);
    Route::get('exhibition/get/{exhibition_id}/sections-information', [ExhibitionController::class, 'GetSectionTaken']);

    Route::get('exhibition/get/{exhibition_id}/ticket-sold-in_place', [ExhibitionController::class, 'GetTicketSoldsInPlace']);
    Route::get('exhibition/get/{exhibition_id}/ticket-sold-virtually', [ExhibitionController::class, 'GetTicketSoldVirtually']);
    Route::get('exhibition/get/{exhibition_id}/ticket-sold-prime', [ExhibitionController::class, 'GetTicketSoldPrime']);

    Route::get('exhibition/get/{exhibition_id}/products-sold', [ExhibitionController::class, 'GetSoldProducts']);

    Route::get('exhibition/get/{exhibition_id}/revenue', [ExhibitionController::class, 'GetRevenue']);
});
