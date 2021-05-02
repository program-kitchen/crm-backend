<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use app/Models/User;

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

    const COLUMNS = 'uuid, name, role, email, is_active, deleted_at';

    /**
    * ユーザ一覧を取得する。
    *
    * @param  Request   $request 検索条件と取得ページ番号
[
  "name" : "[ユーザ名]",
  "role" : "[ユーザ権限]",
  "email" : "[メールアドレス]",
  "withDeleted" : "[削除フラグ(0:未削除ユーザを取得、1:削除済みユーザを含めて取得)]",
  "page" : "[ページネーションの表示位置]"
]    * @return string    ユーザ情報一覧JSON
    */
    public function index(Request $request)
    {
        try {
            $data = $request()->all();
            Log::debug(['request data', $data]);
            if (!$data) {
                throw new Exception('Invalid request data');
            }
            $user = User::selectRaw(self::COLUMNS);
            if (($v = $data['name'])) {
                $user->where('name', 'like', "$v%");
            }
            if (($v = $data['role'])) {
                $user->where('role', $v);
            }
            if (($v = $data['email'])) {
                $user->where('email', 'like', "$v%");
            }
            if (!$data['withDeleted']) {
                $user->whereNull('deleted_at');
            }
            return self::jsonData($user->get()->toArray());
        }
        catch (\Throwable $e) {
            return self::jsonResponse(400, $e->getMessage());
        }
    }

    /**
    * ユーザを取得する。
    *
    * @param  Request   $request    リクエストデータ
    * @param  string    $uuid       ユーザUUID
    * @return ユーザ情報JSON
    */
    public function show(Request $request, string $uuid)
    {
        return self::jsonData(
            User::selectRaw(self::COLUMNS)->where('uuid', $uuid)->toArray());
    }

    /**
    * ユーザ情報を登録する。
    *
    * @param  Request   $request    リクエストデータ
    * @return void
    */
    public function register(Request $request)
    {
        return  "Call user regist";
        User::insertOr
    }

    /**
    * ユーザ情報を削除する。
    *
    * @param  Request   $request    リクエストデータ
    * @return void
    */
    public function delete(Request $request)
    {
        $data = $request()->all();
        User::where('uuid', $data['uuid'])->update(['deleted_at' => 1, 'updated_by' => $data['loginUuid']]);
        return self::jsonData(null);
    }

    /**
    * 削除されたユーザを復活する。
    *
    * @param  Request   $request    リクエストデータ
    * @return void
    */
    public function revive(Request $request)
    {
        $data = $request()->all();
        User::where('uuid', $data['uuid'])->update(['deleted_at' => null, 'updated_by' => $data['loginUuid']]);
        return self::jsonData(null);
    }

    /**
    * ユーザ情報を有効化する。
    *
    * @param  Request   $request    リクエストデータ
    * @return void
    */
    public function activate(Request $request)
    {
        return  "Call user activation";
    }
}
