<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
* ユーザ管理
*
* 機能一覧
* ユーザ一覧を取得する
* ユーザを取得する
* ユーザを登録する
* ユーザを削除する
* 削除されたユーザを復活する
* ユーザを有効化する
*/
class UserController extends Controller
{
    /* 登録チェックルール */
    const REGIST_CHECK_RULE = [
            //'' => 'required|unique:posts|max:255',
            //'body' => 'required'
          ];

    /**
    * ユーザ一覧を取得する。
    *
    * @param  Request   $request 検索条件と取得ページ番号
    *     'name' : ユーザ名
    *     'role' : ユーザ権限
    *     'email': メールアドレス
    *     'withDeleted' : 未削除ユーザを含むかどうか
    *     'page' : ページ
    * @return string    ユーザ情報一覧JSON
    */
    public function index(Request $request)
    {
            return self::dataResponse(
                User::list($request->input('name', ''), $request->input('role', null),
                           $request->input('email', ''), $request->input('withDeleted', false)
                )->get()
//                )->paginate(3)
            );
    }

    /**
    * ユーザを取得する。
    *
    * @param  Request   $request HTTPリクエスト
    * @param  string    $uuid    ユーザUUID
    * @return ユーザ情報JSON
    */
    public function show(Request $request, string $uuid)
    {
        return self::dataResponse(User::getOne($uuid));
    }

    /**
    * ユーザ情報を登録/更新する。
    *
    * 
    * @param  Request   $request HTTPリクエスト
    *     'name'  : ユーザ名
    *     'email' : メールアドレス
    *     'role'  : ユーザ権限
    *     'uuid'  : ユーザuuid 空の時 新規登録, 
    * @return void
    */
    public function register(Request $request)
    {
        $name  = $request->input('name');
        $email = $request->input('email');
        $role  = $request->input('role');
        $uuid  = $request->input('uuid');
        \Log::debug('$name: [' . $name . ']');
        // 入力項目検証
        //$emailRule = Rule::unique('App\Models\User', 'email')->ignore($data['id']);
        $vali = \Validator::make(
            compact('name', 'email', 'role'),
            [
                'name' => 'required|string|max:32',
                // 'email' => 'required|emailmax:256', $emailRule],
                'email'=> 'required|email|max:256',
                'role' => 'required|integer|min:1|max:4',
            ]
        );
        if($vali->fails()){
            return self::badValueResponse($vali->errors());
        }
        User::register($name, $email, $role, $uuid);
        return self::voidResponse();
    }

    /**
    * ユーザ情報を削除する。
    *
    * @param  Request   $request HTTPリクエスト
    * @return void
    */
    public function delete(Request $request)
    {
        User::deleteOne($request->input('uuid'));
        return self::voidResponse();
    }

    /**
    * 削除されたユーザを復活する。
    *
    * @param  Request   $request HTTPリクエスト
    * @return void
    */
    public function revive(Request $request)
    {
        User::revive($request->input('uuid'));
        return self::voidResponse();
    }

    /**
    * ユーザ情報を有効化する。
    *
    * 値検証 TODO
    *
    * @param  Request   $request HTTPリクエスト
    * @return void
    */
    public function activate(Request $request)
    {
        $uuid = $request->input('uuid', '');
        $pw   = $request->input('password', '');
        User::activate($uuid, $pw);
        return self::voidResponse();
    }
}
