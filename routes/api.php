<?php

use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/products/{category_name}', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{category_name}/{product}', [ProductController::class, 'show'])->name('products.show');
