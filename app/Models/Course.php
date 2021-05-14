<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ApiException;
use App\Models\Term;
use Symfony\Component\HttpFoundation\Response;

/*
 * コース情報のモデルクラス
 */
class Course extends Model
{
    use HasFactory;

    // テーブル名
    public const TABLE = 'courses';
    protected $table = self::TABLE;
    // 一覧の取得対象カラム
    const SELECT_COLUMNS = '`id`, `name`, `term`, `summary`, `deleted_at`';

    /**
     * コース一覧を取得する。
     *
     * @param  int    $id  コースID(未指定の場合は全件取得)
     * @return LengthAwarePaginator コース一覧
     */
    public static function list()
    {
        // コース情報取得
        $courses = self::selectRaw(self::SELECT_COLUMNS)
            ->whereNull('deleted_at')
            ->orderByRaw('`id` ASC')
            ->get();

        return $courses;
    }

    /**
     * 指定されたコース情報を取得する。
     *
     * @param  int    $id  コースID(未指定の場合は全件取得)
     * @return LengthAwarePaginator コース一覧
     */
    public static function pickUp(int $id)
    {
        // コース情報取得
        $course = self::selectRaw(self::SELECT_COLUMNS)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();
        // コース情報が取得できなかった場合は既に削除されている
        if (!$course) {
            throw new ApiException(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'コース情報が削除されています。'
            );
        }
        // ターム情報取得
        $terms = Term::list($id);
        $termList = array();
        foreach ($terms as $term) {
            $termList[] = array(
                'name'      => $term['name'],
                'term'      => $term['term'],
                'summary'   => $term['summary'],
            );
        }
        $course['termInfo'] = $termList;

        return $course;
    }

    /**
    * コース情報を登録する。
    *
    * @param  string $name      コース名
    * @param  int    $term      コース期間
    * @param  string $summary   コース概要
    * @param  array  $termList  ターム情報一覧
    *                 name      ターム名
    *                 term      ターム期間
    *                 summary   ターム概要
    * @return void
    */
    public static function register(
        string $name, int $term, string $summary, array $termList
    ) {
        try {
            // ログインユーザ情報取得
            $user = auth()->user();

            // トランザクション開始
            \DB::beginTransaction();
            // コース情報を登録
            $params = compact('name', 'term', 'summary');
            $params['created_by'] = $user->id;
            $params['updated_by'] = $user->id;
            $id = self::insertGetId($params);
            // ターム情報を登録
            Term::register($id, $termList, $user);
            // コミット
            \DB::commit();
        }
        catch (\Exception $e) {
            \Log::Error("コース情報登録失敗\r\n" . $e);
            // ロールバック
            \DB::rollback();
            throw new ApiException("コース情報の登録に失敗しました。");
        }
    }

    /**
     * コース情報を編集する。
     *
     * @param  int    $id        コースID
     * @param  string $name      コース名
     * @param  int    $term      コース期間
     * @param  string $summary   コース概要
     * @param  array  $termList  ターム情報一覧
     *                 name      ターム名
     *                 term      ターム期間
     *                 summary   ターム概要
     * @return void
     */
    public static function edit(
        int $id, string $name, int $term, string $summary, array $termList
    ) {
        try {
            // ログインユーザ情報取得
            $user = auth()->user();

            // トランザクション開始
            \DB::beginTransaction();
            // コース情報を更新
            $params = compact('name', 'term', 'summary');
            $params['updated_by'] = $user->id;
            self::where('id', $id)->update($params);
            // ターム情報を更新
            Term::erase($id);
            Term::register($id, $termList, $user);
            // コミット
            \DB::commit();
        }
        catch (\Exception $e) {
            \Log::Error("コース情報更新失敗\r\n" . $e);
            // ロールバック
            \DB::rollback();
            throw new ApiException("コース情報の更新に失敗しました。");
        }
    }

    /**
     * コース情報を論理削除する。
     *
     * @param  array $ids コースID(複数指定可能)
     * @return void
     */
    public static function erase(array $ids)
    {
        try {
            // ログインユーザ情報取得
            $user = auth()->user();
            // トランザクション開始
            \DB::beginTransaction();
            // コース情報を論理削除
            self::whereIn('id', $ids)->update([
                'deleted_at' => DB::raw('CURRENT_TIMESTAMP'),
                'updated_by' => $user->id,
            ]);
            // コミット
            \DB::commit();
        }
        catch (\Exception $e) {
            \Log::Error("コース情報削除失敗\r\n" . $e);
            // ロールバック
            \DB::rollback();
            throw new ApiException("コース情報の削除に失敗しました。");
        }
    }
}
