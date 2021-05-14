<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

/*
 * ターム情報のモデルクラス
 */
class Term extends Model
{
    use HasFactory;

    // テーブル名
    public const TABLE = 'terms';
    protected $table = self::TABLE;
    // 一覧の取得対象カラム
    const SELECT_COLUMNS = '`course_id`, `order`, `name`, `term`, `summary`';

    /**
     * ターム一覧を取得する。
     *
     * @param  int $courseId コースID
     * @return LengthAwarePaginator コースに紐づくターム一覧
     */
    public static function list(int $courseId)
    {
        // ターム情報取得
        $terms = self::selectRaw(self::SELECT_COLUMNS)
            ->where('course_id', $courseId)
            ->orderByRaw('`order` ASC')
            ->get();

        return $terms;
    }

    /**
    * ターム情報を登録する。
    *
    * @param  int $courseId  コースID
    * @param  array $termList ターム情報一覧
    *           name            ターム名
    *           term            期間
    *           summary         ターム概要
    * @return void
    */
    public static function register(int $courseId, array $termList, User $loginUser)
    {
        $order = 1;
        foreach ($termList as $term) {
            // ターム情報をマージ
            $term['course_id'] = $courseId;
            $term['order'] = $order;
            $term['created_by'] = $loginUser->id;
            // ターム情報を登録
            self::insert($term);
            // 順番をインクリメント
            $order++;
        }
    }

    /**
    * コースに紐づくタームを全て削除する。
    *
    * @param  int $courseId コースID
    * @return void
    */
    public static function erase(int $courseId)
    {
        self::where('course_id', $courseId)->delete();
    }
}
