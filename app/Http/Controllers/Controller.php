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
     * 正常応答(戻り値あり)を返す
     *
     * @param array $data 戻り値
     */
    public static function dataResponse($data)
    {
        return self::jsonResponse(200, $data);
    }

    /**
     * 正常応答(戻り値なし)を返す
     */
    public static function voidResponse()
    {
        return self::jsonResponse(200);
    }

    /**
     * 異常応答を返す
     *
     * @param int       $status     HTTPステータスコード
     * @param string    $message    エラーメッセージ
     */
    public static function errorResponse(int $status, $message)
    {
        $errData = array('errorMsg' => $message);
        return self::jsonResponse($status, $errData);
    }

    /**
     * 応答をJSON形式で返す
     *
     * @param int       $status HTTPステータスコード
     * @param string    $data   応答データ
     */
    public static function jsonResponse(int $status, $data = '')
    {
        return response()->json($data, $status, [], JSON_UNESCAPED_UNICODE);
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
