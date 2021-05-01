<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// 未ログイン状態でアクセス可能
// ログイン
Route::post('/login', [AuthController::class, 'login']);
// ユーザ有効化
Route::post('user/activate', [AuthController::class, 'activate']);

// ログイン中の全ユーザがアクセス可能
Route::group([
    'middleware' => 'auth:api',
], function ($router) {
    // ログアウト
    Route::post('/logout', [AuthController::class, 'logout']);
    // ログイントークン再生成
    Route::post('/refresh', [AuthController::class, 'refresh']);
    // ログインユーザ情報取得
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
});

// ログイン中のバックオフィス権限以上のユーザがアクセス可能
Route::group([
    'middleware' => 'auth:api',
    'middleware' => 'can:back_office',
], function ($router) {
    // ユーザ管理
    Route::prefix('user')->group(function () {
        // ユーザ取得
        Route::get('/index/{uuid?}', [UserController::class, 'index']);
        // ユーザ登録
        Route::post('/register', [UserController::class, 'register']);
        // ユーザ削除
        Route::post('/delete', [UserController::class, 'delete']);
        // ユーザ復活
        Route::post('/revive', [UserController::class, 'revival']);
        
    });

    // コース管理
    Route::prefix('course')->group(function () {
        // コース取得
        Route::get('/index/{id?}', [CourseController::class, 'index']);
        // コース登録
        Route::post('/register', [CourseController::class, 'register']);
        // コース削除
        Route::post('/delete', [CourseController::class, 'delete']);
    });
});
