<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/user', [UserController::class, 'show'])->name('user.show');

    // carts
    Route::prefix('/carts')
    ->controller(CartController::class)
    ->group(function () {
        Route::get('/', 'index')->name('carts.index');
        Route::post('/', 'store')->name('carts.store');
        Route::post('/checkout', 'checkout')->name('carts.checkout');
        Route::post('/{inventory_id}', 'storeReturnInv')->name('carts.storeReturnInv');
        Route::delete('/{cart_id}', 'delete')->name('carts.delete');
        Route::delete('/', 'deleteAll')->name('carts.deleteAll');
        Route::put('/{cart_id}', 'update')->name('carts.update');
    });
});

Route::prefix('/products')
    ->controller(ProductController::class)
    ->group(function () {
        Route::get('/recommend', 'recommend')->name('products.recommend');
        Route::get('/{product}', 'show')->whereNumber('product')->name('products.show');
        Route::get('/', 'index')->name('products.index');
    });

Route::get('/inventories', [InventoryController::class, 'index'])->name('inventories.index');

Route::any('/', function () {
    return 'not found';
});
