<?php

use App\Http\Controllers\ProductController;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/products/recommend', [ProductController::class, 'recommend'])->name('products.recommend');
Route::get('/products/{category_name}/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/{category_name}', [ProductController::class, 'index'])->name('products.index');
Route::get('/products', [ProductController::class, 'indexAll'])->name('products.indexAll');
