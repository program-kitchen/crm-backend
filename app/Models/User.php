<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
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
        $user = self::selectRaw(self::COLUMNS);
        if ($name) {
            $user->where('name', 'like', "%$name%");
        }
        if ($role) {
            $user->where('role', $role);
        }
        if ($email){
            $user->where('email', 'like', "%$email%");
        }
        if (!$withDeleted) {
            $user->whereNull('deleted_at');
        }
        return $user;
    }

    /**
    * ユーザを取得する。
    *
    * @param  string   $uuid    ユーザUUID
    * @return stdClass ユーザ
    */
    public static function getOne(string $uuid)
    {
        return self::whereUuid($uuid)->selectRaw(self::COLUMNS)->first();
    }

    /**
    * ユーザを登録/更新する。
    *
    * 登録者と更新者にログインユーザidを格納する TODO
    *
    * @param  ?string $name  名前の値
    * @param  ?string $email emailの値
    * @param  ?int    $role  権限の値
    * @param  ?string $uuid  uuid 更新対象データキー
    * @return void
    */
    public static function register(?string $name, ?string $email, ?int $role, ?string $uuid)
    {
        $data = compact('name', 'email', 'role');
        \Log::debug(print_r($data, true));
        if ($uuid && self::updateByUuid($uuid, $data)) {
            return;
        }
        $data['created_by'] = $data['updated_by'] = 1000;
        self::Insert($data);
    }

    /**
    * ユーザを削除する。
    *
    * @param  string $uuid ユーザUUID
    * @return void
    */
    public static function deleteOne(string $uuid)
    {
        self::updateByUuid($uuid, ['deleted_at' => DB::raw('CURRENT_TIMESTAMP')]);
    }

    /**
    * 削除されたユーザを復活する。
    *
    * @param  string $uuid ユーザUUID
    * @return void
    */
    public static function revive(string $uuid)
    {
        self::updateByUuid($uuid, ['deleted_at' => null]);
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
        self::updateByUuid($uuid, ['password' => $password, 'is_active' => 1]);
    }

    /**
    * ユーザを更新する。
    *
    * 更新者にログインユーザid(?uuid) を設定する TODO
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
        return self::whereRaw('uuid = UUID_TO_BIN(?)', [$uuid]);
    }
}
