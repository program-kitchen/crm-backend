<?php

namespace App\Models;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use App\Notifications\CustomPasswordReset;
use App\Notifications\VerifyEmail;
use App\Exceptions\ApiException;
use Carbon\Carbon ;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Symfony\Component\HttpFoundation\Response;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasFactory, Notifiable;

    public const TABLE = 'users';
    protected $table = self::TABLE;

    const COLUMNS = 'uuid, name, role, email, is_active, deleted_at';

    //protected $guarded = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
    * 一覧を取得する。
    *
    * @param ?string name        ユーザ名の検索条件   部分一致
    * @param ?int    role        ユーザ権限の検索条件
    * @param ?string email       メールアドレスの検索条件 部分一致
    * @param ?bool   withDeleted 削除ユーザを含めるかどうか
    * @return Illuminate\Pagination\LengthAwarePaginator ユーザ一覧
    */
    public static function list(
        ?string $name, ?int $role, ?string $email, ?bool $withDeleted
    ) {
        $query = self::selectRaw(self::COLUMNS);
        if ($name) {
            $query->where('name', 'like', "%$name%");
        }
        if ($role) {
            $query->where('role', $role);
        }
        if ($email){
            $query->where('email', 'like', "%$email%");
        }
        if (!$withDeleted) {
            $query->whereNull('deleted_at');
        }
        return $query->get();
    }

    /**
    * ユーザを取得する。
    *
    * @param  string   $uuid    ユーザUUID
    * @param  bool     $isGetId ユーザID取得フラグ
    * @return stdClass ユーザ
    */
    public static function pickUp(string $uuid, bool $isGetId = false)
    {
        $columns = self::COLUMNS;
        if ($isGetId) {
            $columns .= ', id';
        }
        $user = self::selectRaw($columns)
            ->whereUuid($uuid)
            ->whereNull('deleted_at')
            ->first();
        // ユーザ情報が取得できなかった場合は既に削除されている
        if (!$user) {
            throw new ApiException(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'ユーザ情報が削除されています。'
            );
        }

        return $user;
    }

    /**
    * ユーザ認証トークンの検証を行う。
    * トークンが有効期間内であれば対象のユーザ情報を取得する。
    * 有効期間を過ぎていた場合はエラー応答を返す。
    *
    * @param  string   $token ユーザ認証トークン
    * @return stdClass ユーザ情報
    */
    public static function pickUpAtToken(string $token)
    {
        // トークンからユーザ情報を取得
        $now = Carbon::now()->toDateTimeString();
        \Log::info('日時：' . $now);
        $user = self::selectRaw(self::COLUMNS)
            ->where('token', $token)
            ->where('token_validity_period', '>', $now)
            ->first();
        // ユーザ情報が取得できなかった場合は有効期間切れ
        if (!$user) {
            throw new ApiException(
                Response::HTTP_BAD_REQUEST,
                'ユーザ認証の有効期間が切れています。'
            );
        }

        return $user;
    }

    /**
    * ユーザを登録/更新する。
    *
    * 登録者と更新者にログインユーザidを格納する
    *
    * @param  ?string $name  名前の値
    * @param  ?string $email emailの値
    * @param  ?int    $role  権限の値
    * @param  ?string $uuid  uuid 更新対象データキー
    * @return 更新時: null, 
    *         登録時: string 本登録トークン
    */
    public static function register(
        ?string $name, ?string $email, ?int $role, ?string $uuid
    ) {
        // ログインユーザ情報取得
        $user = auth()->user();

        // UUIDが指定されていれば更新
        $data = compact('name', 'email', 'role');
        $data['updated_by'] = $user->id;
        if ($uuid && self::updateByUuid($uuid, $data)) {
            return null;
        }

        // 登録時は認証用トークンと有効期間を登録する
        $tokenInfo = self::createNewToken($email);
        $data = array_merge($data, $tokenInfo);
        $data['created_by'] = $user->id;
        self::insert($data);

        return $tokenInfo['token'];
    }

    /**
    * ユーザを削除する。
    *
    * @param  array $uuids ユーザUUID(複数指定可能)
    * @return void
    */
    public static function erase(array $uuids)
    {
        try {
            // ログインユーザ情報取得
            $user = auth()->user();

            // トランザクション開始
            \DB::beginTransaction();
            // 削除日を登録
            $data = array(
                'deleted_at' => DB::raw('CURRENT_TIMESTAMP'),
                'updated_by' => $user->id
            );
            self::whereIn('uuid', $uuids)
                ->update($data);
            // コミット
            \DB::commit();
        }
        catch (\Exception $e) {
            \Log::Error("ユーザ情報削除失敗\r\n" . $e);
            // ロールバック
            \DB::rollback();
            throw new ApiException("ユーザ情報の削除に失敗しました。");
        }
        
    }

    /**
    * 削除されたユーザを復活する。
    *
    * @param  string $uuid ユーザUUID
    * @return void
    */
    public static function revive(string $uuid)
    {
        // ログインユーザ情報取得
        $user = auth()->user();

        // 削除日を未指定に変更
        $data = array(
            'deleted_at' => null,
            'updated_by' => $user->id
        );
        self::updateByUuid($uuid, $data);
    }

    /**
    * ユーザを有効化する。
    *
    * @param  string $uuid     ユーザUUID
    * @param  string $password パスワード
    * @return void
    */
    public static function activate(string $uuid, string $password)
    {
        // パスワードを暗号化
        $password = \Crypt::encrypt($password);

        // 未ログインの為、ログインユーザ情報をDBから取得
        $user = self::pickUp($uuid, true);

        // パスワードとアクティブフラグを更新
        $data = array(
            'deleted_at' => null,
            'updated_by' => $user->id
        );
        // パスワードと有効フラグを更新
        self::updateByUuid($uuid, ['password' => $password, 'is_active' => 1]);
    }

    /**
    * パスワードリセット用の有効期間付きトークンを生成する
    *
    * @param  string $uuid  ユーザUUID
    * @param  string $email メールアドレス
    * @return string パスワードリセット用トークン
    */
    public static function createResetToken(string $uuid, string $email)
    {
        // ログインユーザ情報取得
        $user = auth()->user();

        // パスワードリセット用トークン生成
        $tokenInfo = self::createNewToken($email);

        // 認証用トークンと有効期間を更新
        $data = array(
            'updated_by' => $user->id,
        );
        $data = array_merge($data, $tokenInfo);
        self::updateByUuid($uuid, $data);

        return $tokenInfo['token'];
    }

    /**
    * ユーザを更新する。
    *
    * 更新者にログインユーザid(?uuid) を設定する
    *
    * @param  string $uuid ユーザUUID
    * @param  array  $data 更新データ
    * @return 更新件数
    */
    private static function updateByUuid(string $uuid, array $data)
    {
        return self::whereUuid($uuid)->update($data);
    }

    /**
    * uuid の検索条件を設定するユーザを更新する。
    *
    * @param  string $uuid ユーザUUID
    * @return uuid の検索条件
    */
    private static function whereUuid(string $uuid)
    {
        return self::where('uuid', $uuid);
    }

    /**
    * メールアドレスを元にトークンを作成する。
    *
    * @param  string $email メールアドレス
    * @return array $tokenInfo
    *       token                   トークン
    *       token_validity_period   トークン有効期間
    */
    private static function createNewToken(string $email)
    {
        $token = md5(rand(0, 9) . $email . time());
        $validityMinute = config('const.token_valide_minute');
        $now = Carbon::now();
        $tokenInfo = array(
            'token'                 => $token,
            'token_validity_period' => $now->addMinutes($validityMinute),
        );

        return $tokenInfo;
    }
}
