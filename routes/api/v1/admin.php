<?php

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\ExhibitionController;
use App\Http\Controllers\Api\V1\MapController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\UserController;

use Illuminate\Support\Facades\Route;


Route::get('exhibition/section/get/{section_id}', [MapController::class, 'GetCompanyAuction'])->name('section-read');
Route::patch('exhibition/section/select', [MapController::class, 'Select'])->name('section-update');

Route::group([
    'middleware' => [
        'auth:api',
        'check_permissions',
    ]
], function () {
    Route::patch('user/change-state', [UserController::class, 'ChangeState'])->name('user-change-state');
    Route::delete('user/delete', [UserController::class, 'Delete'])->name('user-delete');
    Route::get('user/get', [UserController::class, 'GetUsers'])->name('user-read');
    Route::get('user/get/{user_id}', [UserController::class, 'GetUser'])->name('user-read-details');

    Route::patch('document/update', [DocumentController::class, 'UpdateInfo'])->name('document-update');
    Route::get('document/download/{document_id}', [DocumentController::class, 'GetDocument'])->name('document-download');
    Route::get('document/get/{document_id}', [DocumentController::class, 'GetDocument'])->name('document-read-details');

    Route::patch('company/change-state', [CompanyController::class, 'ChangeState'])->name('company-change-state');

    Route::get('company/get', [CompanyController::class, 'AdminGetCompanies'])->name('company-read');
    Route::get('company/get/need-approval', [CompanyController::class, 'AdminGetPendingCompanies'])->name('company-read-pending');
    Route::get('company/get/{company_id}', [CompanyController::class, 'AdminGetCompany'])->name('company-read-details');

    Route::put('exhibition/create', [ExhibitionController::class, 'Create'])->name('exhibition-create');
    Route::patch('exhibition/update', [ExhibitionController::class, 'Update'])->name('exhibition-update');
    Route::patch('exhibition/change-state', [ExhibitionController::class, 'ChangeState'])->name('exhibition-change-state');

    Route::get('exhibition/get', [ExhibitionController::class, 'AdminGetExhibitions'])->name('exhibition-read');
    Route::get('exhibition/get/{exhibition_id}', [ExhibitionController::class, 'AdminGetExhibition'])->name('exhibition-read-details');

    Route::put('category/create', [CategoryCOntroller::class, 'Create'])->name('category-create');
    Route::patch('category/update', [CategoryCOntroller::class, 'Update'])->name('category-update');
    Route::delete('category/delete', [CategoryCOntroller::class, 'Delete'])->name('category-delete');
    Route::get('category/get', [CategoryController::class, 'Get'])->name('category-read');

    Route::delete('product/delete', [ProductController::class, 'Delete'])->name('product-delete');
    Route::get('product/get/', [ProductController::class, 'AdminGetProducts'])->name('product-read');
    Route::get('product/get/{product_id}', [ProductController::class, 'AdminGetProduct'])->name('product-read-details');

    Route::get('order/get', [OrderController::class, 'Get'])->name('order-read');
    Route::get('order/get/{order_id}', [OrderController::class, 'GetOrder'])->name('order-read-details');
    Route::patch('order/change-state', [OrderController::class, 'ChangeState'])->name('order-change-state');
});
