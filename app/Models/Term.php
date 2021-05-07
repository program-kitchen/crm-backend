<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Exception;

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
    const SELECT_COLUMNS = 'course_id, order, name, term, summary';

    /**
     * ターム一覧を取得する。
     *
     * @param  int    courseId  コースID(未指定の場合は全件取得)
     * @return LengthAwarePaginator コースに紐づくターム一覧
     */
    public static function list(int $courseId = 0)
    {
        // ターム情報取得
        $term = self::selectRaw(self::SELECT_COLUMNS)->
                orderByRaw('order ASC');
        // コースIDが指定されている場合は絞り込み
        if ($courseId > 0) {
            $term->where('course_id', $courseId);
        }

        return $term;
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
    public static function createAll(int $courseId, array $termList, User $loginUser)
    {
        $order = 1;
        foreach ($termList as $term) {
            \Log::Debug("order：". $order);
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
    public static function deleteAll(int $courseId)
    {
        self::where('course_id', $courseId)->delete();
    }
}
