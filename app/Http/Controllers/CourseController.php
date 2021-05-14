<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;

/**
 * コース管理用コントローラクラス。
 */
class CourseController extends Controller
{
    // コース登録検証ルール
    const COURSE_REGISTER_RULE = [
        'name'      => ['required', 'max:32'],
        'term'      => ['required', 'integer', 'between:1,52'],
        'summary'   => ['max:256'],
    ];
    // ターム登録検証ルール
    const TERM_REGISTER_RULE = [
        'name'      => ['required', 'max:32'],
        'term'      => ['required', 'integer', 'between:1,26'],
        'summary'   => ['max:256'],
    ];

    /**
    * コース情報とそれに紐づくターム情報を取得する。
    *
    * @param  Request   $request    リクエストデータ
    * @return コース情報JSON
    */
    public function index(Request $request)
    {
        // デバッグログ出力
        \Log::Debug("コース情報一覧取得");

        // コース情報一覧を取得
        $courses = Course::list();

        // レスポンス送信
        return self::dataResponse($courses);
    }

    /**
     * コース情報を取得する。
     *
     * @param  Request   $request HTTPリクエスト
     * @param  int    $id    コースID
     * @return コース情報JSON
     */
    public function show(Request $request, int $id)
    {
        // デバッグログ出力
        \Log::Debug("コース情報取得：". $id);

        // コース情報取得
        $course = Course::pickUp($id);

        // レスポンス送信
        return self::dataResponse($course);
    }

    /**
    * コース情報と紐づくターム情報を登録する。
    *
    * @param  Request   $request    リクエストデータ
    * @return \Symfony\Component\HttpFoundation\ParameterBag|mixed
    */
    public function register(Request $request)
    {
        // デバッグログ出力
        $params = request(['id', 'name', 'term', 'summary', 'termInfo']);
        \Log::Debug("コース情報登録：". self::arrayToString($params));

        // 入力チェック実施
        $this->validateArray($params, self::COURSE_REGISTER_RULE);
        foreach ($params['termInfo'] as $term) {
            $this->validateArray($term, self::TERM_REGISTER_RULE);
        }

        // コース情報、ターム情報を登録
        if ($params['id'] > 0) {
            Course::edit(
                $params['id'],
                $params['name'],
                $params['term'],
                $params['summary'],
                $params['termInfo']
            );
        } else {
            Course::register(
                $params['name'],
                $params['term'],
                $params['summary'],
                $params['termInfo']
            );
        }

        // レスポンス送信
        return self::voidResponse();
    }

    /**
     * コース情報と紐づくターム情報を削除する。
     *
     * @param  Request   $request    リクエストデータ
     * @return \Symfony\Component\HttpFoundation\ParameterBag|mixed
     */
    public function delete(Request $request)
    {
        // デバッグログ出力
        $params = request(['id']);
        \Log::Debug("コース情報削除：". var_export($params, true));

        // コース情報を論理削除
        $ids = explode(",", $params['id']);
        Course::erase($ids);

        // レスポンス送信
        return self::voidResponse();
    }
}
