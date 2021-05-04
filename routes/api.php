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

const UUID_REGEX = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';

// 未ログイン状態でアクセス可能
// ログイン
Route::post('/login', [AuthController::class, 'login']);
// ユーザ有効化
Route::post('user/activate', [UserController::class, 'activate']);

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
        // 一覧
        Route::get('', [UserController::class, 'index']);
        // 取得
        Route::get('/{uuid}', [UserController::class, 'show']
            )->where('uuid', UUID_REGEX);
        // 登録
        Route::post('/register', [UserController::class, 'register']);
        // 削除
        Route::post('/delete', [UserController::class, 'delete']);
        // 復活
        Route::post('/revive', [UserController::class, 'revive']);
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
