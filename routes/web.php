<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    // dd(Storage::url('uwn3iIPNyLBDR9qO0liRcTi1uKa0n4pYp8iQhWrc.jpg'));
    // http://localhost:8888/storage/product_img/uwn3iIPNyLBDR9qO0liRcTi1uKa0n4pYp8iQhWrc.jpg
    return view('test');
});

Route::POST('/upload', function (Request $request) {
    // dd($request);
    $request->file('avatar')->store('product_img', 'public');
    return 'OKOK';
});

Route::get('/category', function () {
    // json轉譯中文
    return response()->json(\App\Models\Category::all(), 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
});
