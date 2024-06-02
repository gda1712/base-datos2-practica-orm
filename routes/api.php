<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::prefix('/product')->group(function () {
    Route::post('/', [ProductController::class, 'store']);
});
