<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * コース管理用コントローラクラス。
 */
class CourseController extends Controller
{
    /* 登録チェックルール */
    const REGIST_CHECK_RULE = [
            //'' => 'required|unique:posts|max:255',
            //'body' => 'required'
          ];
    /**
    * コース情報とそれに紐づくターム情報を取得する。
    *
    * @param  Request   $request    リクエストデータ
    * @param  int       $id         コースID(未指定の場合は全件取得)
    * @return コース情報JSON
    */
    public function index(Request $request, int $id = 0)
    {
        return  "Call course index id=" . $id;
    }

    /**
    * コース情報と紐づくターム情報を登録する。
    *
    * @param  Request   $request    リクエストデータ
    * @return コース情報JSON
    */
    public function register(Request $request)
    {
        return  "Call course regist";
    }

    /**
    * コース情報と紐づくターム情報を削除する。
    *
    * @param  Request   $request    リクエストデータ
    * @return コース情報JSON
    */
    public function delete(Request $request)
    {
        return  "Call course delete";
    }
}
