<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/user', [UserController::class, 'show'])->name('user.show');
    Route::post('/logout', [UserController::class, 'logout'])->name('user.logout');
});

Route::prefix('/products')
    ->controller(ProductController::class)
    ->group(function () {
        Route::get('/recommend', 'recommend')->name('products.recommend');
        Route::get('/{product}', 'show')->whereNumber('product')->name('products.show');
        Route::get('/', 'index')->name('products.index');
    });

Route::post('/register', [UserController::class, 'register'])->name('user.register');
Route::post('/login', [UserController::class, 'login'])->name('user.login');
