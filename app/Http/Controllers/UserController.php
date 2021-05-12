<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\User;
use Validator;
use Mail;

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
    /** 一覧検索条件 検証内容 */
    const INDEX_RULES = [
        'role'        => ['nullable', 'integer', 'max:100'],
        'withDeleted' => ['boolean'],
        'page'        => ['nullable', 'integer', 'min:1'],
    ];

    /** 登録/更新 項目検証内容 */
    const STORE_RULES = [
        'name' => ['required', 'string', 'max:32'],
        'email'=> ['required', 'email', 'max:256'],
        'role' => ['required', 'integer', 'between:1,4'],
    ];

    /** UUID必須 検証内容 */
    const UUID_REQUIRED_RULES = [
        'uuid' => ['uuid'],
    ];

    /** ユーザ有効化 項目 検証内容 */
    const ACTIVATE_RULES = [
        'uuid'     => ['required', 'uuid'],
        'password' => ['required', 'pass_valid', 'between:8,15', 'pass_format'],
    ];

    /**
    * ユーザ一覧を取得する。
    *
    * 入力検索条件のうち 下記項目の値を検証し正しければユーザを一覧を取得し、
    * ユーザ一覧を含む正常応答を返す。
    * 正しくない時はエラー応答を返す。
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
        $this->validate($request, self::INDEX_RULES);

        return self::dataResponse(
            User::list($request->input('name'), $request->input('role'),
                       $request->input('email'), $request->input('withDeleted')
            )
        );
    }

    /**
    * ユーザを取得する。
    *
    * 入力値 UUIDの値を検証し正しければユーザを取得し、ユーザ情報を含む正常応答を返す。
    * 正しくない時はエラー応答を返す。
    *
    * @param  Request   $request HTTPリクエスト
    * @param  string    $uuid    取得対象ユーザUUID
    * @return ユーザ情報JSON
    */
    public function show(Request $request, string $uuid)
    {
        $this->validate($request, self::UUID_REQUIRED_RULES);
        return self::dataResponse(User::pickUp($uuid));
    }

    /**
    * ユーザ情報を登録/更新する。
    *
    * 入力項目を検証し正しければユーザを登録または更新し、正常応答を返す。
    * 正しくない時はエラー応答を返す。
    * 入力項目の uuid がない、null, '' の時は登録し、値があれば更新する。
    *
    * @param  Request   $request HTTPリクエスト
    *     'name'  : ユーザ名
    *     'email' : メールアドレス
    *     'role'  : ユーザ権限
    *     'uuid'  : ユーザuuid 空の時 新規登録
    * @return void
    */
    public function register(Request $request)
    {
        
        $params = request(['name', 'email', 'role', 'uuid']);

        // 入力項目検証
        $rules = self::STORE_RULES;
        if (!empty($params['uuid'])) {
            $rules = array_merge($rules, self::UUID_REQUIRED_RULES);
            $rules['email'][] = 'unique:users,email,'. $params['uuid'] .',uuid';
        } else {
            $rules['email'][] = Rule::unique('users');
        }
        $this->validate($request, $rules);

        // ユーザ情報登録
        User::register(
            $params['name'], $params['email'],
            $params['role'], $params['uuid']
        );
        if (!empty($params['uuid'])) {
            return self::voidResponse();
        }

        // 登録時は本登録トークン付きの url を email で送る
        /*if (empty($params['uuid'])) {
            $name = $params['name'];
            $email = $params['email'];
            Mail::send('emails.admit', compact('name', 'token'),
                function($message) use ($params) {
                    $message->to($params['email'], $params['name'])
                    ->subject('【COACHTECH-CRM】本登録のお願い');
            });
        }*/
        return self::voidResponse();
    }

    /**
    * ユーザ情報を削除する。
    *
    * 入力値 UUIDの値を検証し正しければユーザを削除し、正常応答を返す。
    * 正しくない時はエラー応答を返す。
    *
    * @param  Request   $request HTTPリクエスト
    *    uuid 削除対象ユーザUUID
    * @return void
    */
    public function delete(Request $request)
    {
        $this->validate($request, self::UUID_REQUIRED_RULES);
        User::erase($request->input('uuid'));
        return self::voidResponse();
    }

    /**
    * 削除されたユーザを復活させる。
    *
    * 入力値 UUIDの値を検証し正しければユーザを復活させ、正常応答を返す。
    * 正しくない時はエラー応答を返す。
    *
    * @param  Request   $request HTTPリクエスト
    *    uuid 復活対象ユーザUUID
    *
    * @return void
    */
    public function revive(Request $request)
    {
        $this->validate($request, self::UUID_REQUIRED_RULES);
        User::revive($request->input('uuid'));
        return self::voidResponse();
    }

    /**
    * ユーザ情報を有効化する。
    *
    * 入力値 UUIDとパスワードの値を検証し正しければユーザを有効化する。
    * 正しくない時はエラー応答を返す。
    *
    * @param  Request   $request HTTPリクエスト
    *    uuid 有効化対象ユーザUUID
    *    password ユーザに設定するパスワード
    * @return void
    */
    public function activate(Request $request)
    {
        $this->validate($request, self::ACTIVATE_RULES);
        User::activate($request->input('uuid'), $request->input('password'));
        return self::voidResponse();
    }
}
