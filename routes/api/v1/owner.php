<?php
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ExhibitionController;
use App\Http\Controllers\Api\V1\MapController;
use App\Http\Controllers\Api\V1\ProductController;

use Illuminate\Support\Facades\Route;


Route::group([
    'middleware' => [
        'auth:api',
        'check_company_onwer',
    ]
], function () {

    Route::put('product/create', [ProductController::class, 'Create']);
    Route::patch('product/update', [ProductController::class, 'Update']);
    Route::delete('product/delete', [ProductController::class, 'Delete']);
    Route::get('product/get', [ProductController::class, 'GetMyProducts']);
    Route::get('product/get/{product_id}', [ProductController::class, 'GetMyProduct']);

    Route::get('category/get', [CategoryController::class, 'Get']);

    Route::get('exhibition/get/pending', [ExhibitionController::class, 'GetPendingExhibitions']);
    Route::get('exhibition/get/pending/{exhibition_id}', [ExhibitionController::class, 'GetPendingExhibition']);

    Route::put('exhibition/section/create', [MapController::class, 'CreateAuction']);
    Route::get('exhibition/section/get', [MapController::class, 'GetAllMySectionAuction']);
    Route::get('exhibition/section/get/{section_id}', [MapController::class, 'GetSectionAuction']);
    Route::get('exhibition/section/{exhibition_id}/get', [MapController::class, 'GetMySectionAuction']);
});