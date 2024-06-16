<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\MovementController;

Route::prefix('/product')->group(function () {
    Route::post('/', [ProductController::class, 'store']);
    Route::get('/{id}', [ProductController::class, 'show']);
});

Route::prefix('/movement')->group(function () {
    Route::post('/with-orm', [MovementController::class, 'storeMovementWithORM']);
    Route::post('/without-orm', [MovementController::class, 'storeMovementWithoutORM']);
});
