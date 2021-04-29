<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * ユーザ管理用コントローラクラス。
 */
class UserController extends Controller
{
    /* 登録チェックルール */
    const REGIST_CHECK_RULE = [
            //'' => 'required|unique:posts|max:255',
            //'body' => 'required'
          ];
    /**
    * ユーザ情報を取得する。
    *
    * @param  Request   $request    リクエストデータ
    * @param  string    $uuid       ユーザUUID(未指定の場合は全件取得)
    * @return ユーザ情報JSON
    */
    public function index(Request $request, string $uuid = '')
    {
        return json_encode([
            "text" => "Call user index uuid=" . $uuid,
            // 'token' => $request->session()->token()
        ]); 
    }

    /**
    * ユーザ情報を登録する。
    *
    * @param  Request   $request    リクエストデータ
    * @return void
    */
    public function regist(Request $request)
    {
        return "Call user regist " . implode(':', $request->all());
    }

    /**
    * ユーザ情報を削除する。
    *
    * @param  Request   $request    リクエストデータ
    * @return void
    */
    public function delete(Request $request)
    {
        return  "Call user delete";
    }

    /**
    * 削除されたユーザを復活する。
    *
    * @param  Request   $request    リクエストデータ
    * @return void
    */
    public function revival(Request $request)
    {
        return  "Call user revival";
    }

    /**
    * ユーザ情報を有効化する。
    *
    * @param  Request   $request    リクエストデータ
    * @return void
    */
    public function activation(Request $request)
    {
        return  "Call user activation";
    }
}
