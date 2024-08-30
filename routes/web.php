<?php

use Illuminate\Support\Facades\Route;

Route::get('login', function () {
    return response([
        'status' => 'failed',
        'error' => 'Please log in.'
    ], 401);
})->name('login');
