<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

/*
 * コントローラの基底クラス
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Create a new Controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('JpJsonResponse');
    }

    /**
     * 入力チェックを行う
     * ※エラーの場合はValidationExceptionが発行され、
     *   エラーハンドラクラスからエラー応答が返される。
     *
     * @param string    $params パラメータ
     * @param array     $rules  入力チェックルール
     */
    protected function validateArray(array $params, array $rules)
    {
        $validator = \Validator::make($params, $rules);
        $validator->validate();
    }

    /**
     * 入力チェックを行う
     * ※エラーの場合はValidationExceptionが発行され、
     *   エラーハンドラクラスからエラー応答が返される。
     *
     * @param Request   $request    リクエストデータ
     * @param array     $rules      入力チェックルール
     */
    protected function validate(Request $request, array $rules)
    {
        $this->validateArray($request->all(), $rules);
    }

    /**
    * 認証済みでないかトークンが無効の時のjson応答を返す。
    *
    */
    public static function unauthorized()
    {
        return self::jsonResponse(401, 'Unauthorized');
    }

    /**
    *
    */
    public static function dataResponse($data)
    {
        return self::jsonResponse(200, $data);
    }

    /**
    *
    */
    public static function voidResponse()
    {
        return self::jsonResponse(200);
    }

    /**
    *
    */
    public static function errorResponse(int $status, $message)
    {
        return self::jsonResponse($status, $message);
    }

    /**
    *
    */
    public static function jsonResponse(int $status, $data = '')
    {
        return response()->json($data, $status, [], JSON_UNESCAPED_UNICODE);
    }

    /**
    * json用 api 共通部分。
    *
    * @param  string $method DB操作機能の関数名
    */
    private function apiCommon(callable $method)
    {
        try {
            $data = request()->all();
            Log::debug(['request data', $data]);
            if (!$data) {
                throw new Exception('Invalid request data');
            }
            $res = call_user_func_array($method, [$data]);
            return self::jsonResponse(200, null);
        }
        catch (\Throwable $e) {
            return self::jsonResponse(500, $e->getMessage());
        }
    }

    /**
     * 配列を文字列に変換する。
     * @param  array $array 変換対象の配列
     */
    public static function arrayToString(array $array)
    {
        $str = var_export($array, true);
        return str_replace(array("\r\n", "\r", "\n"), '', $str);
    }
}
