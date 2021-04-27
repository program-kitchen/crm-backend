<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


// ユーザ管理
Route::prefix('user')->group(function () {
    // ユーザ取得
    Route::get('/index/{uuid?}', [UserController::class, 'index']);
    // ユーザ登録
    Route::post('/regist', [UserController::class, 'regist']);
    // ユーザ削除
    Route::post('/delete', [UserController::class, 'delete']);
    // ユーザ復活
    Route::post('/revival', [UserController::class, 'revival']);
    // ユーザ有効化
    Route::post('/activation', [UserController::class, 'activation']);
});

// コース管理
Route::prefix('course')->group(function () {
    // コース取得
    Route::get('/index/{id?}', [CourseController::class, 'index']);
    // コース登録
    Route::post('/regist', [CourseController::class, 'regist']);
    // コース削除
    Route::post('/delete', [CourseController::class, 'delete']);
});