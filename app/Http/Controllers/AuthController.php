<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;
use Symfony\Component\HttpFoundation\Response;

/*
 *  ログイン認証用コントローラクラス
 */
class AuthController extends Controller
{
    // ログイン入力チェックルール
    const LOGIN_CHECK_RULE = [
        'email'    => ['required', 'max:256'],
        'password' => ['required', 'between:8,15'],
    ];

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // 入力チェック実施
        $this->validate($request, self::LOGIN_CHECK_RULE);

        // ログイン認証実施
        $credentials = request(['email', 'password']);
        if (! $token = auth()->attempt($credentials)) {
            return self::errorResponse(
                Response::HTTP_UNAUTHORIZED,
                'ユーザID、パスワードが一致しません。'
            );
        }

        // 情報ログ出力
        \Log::Info("user login: id=" . auth()->user()->id);

        // トークンを発行し、レスポンス送信
        return $this->createNewToken($token);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        // ログアウト処理
        $userId = auth()->user()->id;
        auth()->logout();

        // 情報ログ出力
        \Log::Info("user logout: id=" . $userId);

        // レスポンス送信 
        return self::voidResponse();
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }

}