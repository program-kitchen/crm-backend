<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use App\Notifications\CustomPasswordReset;
use App\Notifications\VerifyEmail;

use Tymon\JWTAuth\Contracts\JWTSubject;

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
    public static function list(?string $name, ?int $role, ?string $email, ?bool $withDeleted)
    {
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
        return self::whereUuid($uuid)->selectRaw($columns)->first();
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
    public static function register(?string $name, ?string $email, ?int $role, ?string $uuid)
    {
        // ログインユーザ情報取得
        $user = auth()->user();

        // UUIDが指定されていれば更新
        $data = compact('name', 'email', 'role');
        $data['updated_by'] = $user->id;
        if ($uuid && self::updateByUuid($uuid, $data)) {
            return null;
        }
        // 指定されていなければ登録
        $data['created_by'] = $user->id;
        self::insert($data);
    }

    /**
    * ユーザを削除する。
    *
    * @param  string $uuid ユーザUUID
    * @return void
    */
    public static function erase(string $uuid)
    {
        // ログインユーザ情報取得
        $user = auth()->user();

        // 削除日を登録
        $data = array(
            'deleted_at' => DB::raw('CURRENT_TIMESTAMP'),
            'updated_by' => $user->id
        );
        self::updateByUuid($uuid, $data);
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

    /*
     *
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomPasswordReset($token));
    }

    /*
     *
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }
}
