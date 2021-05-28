<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use App\Exceptions\ApiException;
use App\Mail\ResetPass;
use App\Mail\UserActivation;
use App\Models\User;
use Validator;
use Symfony\Component\HttpFoundation\Response;

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

    /** ユーザ有効化トークン検証 項目 検証内容 */
    const VALIDATE_TOKEN_RULES = [
        'token' => ['required'],
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
        $token = User::register(
            $params['name'], $params['email'],
            $params['role'], $params['uuid']
        );
        if (!$token) {
            return self::voidResponse();
        }

        // 登録時は認証用トークン付きの url を email で送る
        $this->sendMail($params['email'], $params['name'], $token);

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
        // 入力項目検証
        $uuids = explode(",", $request->input('uuid'));
        foreach ($uuids as $uuid) {
            $this->validateArray(array($uuid), self::UUID_REQUIRED_RULES);
        }

        // ユーザを削除
        User::erase($uuids);
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

    /**
     * ユーザ有効化用トークンの検証を行う。
     * 有効期間内のトークンだった場合はユーザ情報を返す。
     *
     * @param  Request   $request HTTPリクエスト
     * @return ユーザ情報JSON
     */
    public function validateToken(Request $request)
    {
        $this->validate($request, self::VALIDATE_TOKEN_RULES);
        $user = User::pickUpAtToken($request->input('token'));
        return self::dataResponse($user);
    }

    /**
     * ユーザのパスワードリセットメールを送信する。
     *
     * @param  Request   $request HTTPリクエスト
     *    uuid 対象ユーザUUID
     * @return void
     */
    public function resetPassword(Request $request)
    {
        // 引数検証
        $this->validate($request, self::UUID_REQUIRED_RULES);

        // パスワードリセット用メールを送信
        $user = User::pickUp($request->input('uuid'));
        $token = User::createResetToken($user->uuid, $user->email);
        $this->sendMail($user->email, $user->name, $token, true);

        return self::voidResponse();
    }

    /**
     * メールを送信する。
     *
     * @param  string   $params         リクエストパラメータ
     * @param  string   $name         リクエストパラメータ
     * @param  string   $token          認証用トークン
     * @param  bool     $isResetPass    パスワードリセットフラグ
     *                      true:パスワードリセットメール送信
     *                      false:ユーザ認証メール送信
     * @return void
     */
    private function sendMail(
        string $email, string $toName, string $token, bool $isResetPass = false
    ) {
        // 環境に応じてフロントエンドのURLを生成
        $env = 'local';
        if (env('APP_DEBUG', false)) {
            $env = 'product';
        }
        $url = config('const.frontend')[$env] .
               config('const.frontend')['activation'] .
               $token;

        // メール送信
        try {
            $message = $isResetPass ?
                new ResetPass($toName, $url) :
                new UserActivation($toName, $url);
            Mail::to($email)->send($message);
        } catch (\Exception $e) {
            \Log::Error("メール送信失敗\r\n" . $e);
            throw new ApiException(
                Response::HTTP_BAD_REQUEST,
                "メールの送信に失敗しました。\r\n" .
                "メールアドレスが正しいかご確認下さい。\r\n" .
                "正しい場合はお手数ですが、システム管理者へお問い合わせください。"
            );
        }
    }
}
