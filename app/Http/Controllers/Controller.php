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
abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 入力チェックを行う
     * ※エラーの場合はValidationExceptionが発行され、
     *   エラーハンドラクラスからエラー応答が返される。
     *
     * @param Request   $request    リクエストデータ
     * @param array     $rules      入力チェックルール
     */
    protected function validate(Request $request, $rules)
    {
        $validator = \Validator::make($request->all(), $rules);
        $validator->validate();
    }
}
