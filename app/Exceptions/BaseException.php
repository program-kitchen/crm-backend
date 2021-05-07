<?php

namespace App\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use RuntimeException;

/*
 * 基底例外クラス
 * 例外発生時のエラー応答をカスタマイズするために作成
 */
class BaseException extends RuntimeException implements Responsable
{
    /**
     * @var 連想配列 エラーメッセージ定義
     *      key int HTTPステータスコード
     *      value string エラーメッセージ
     */
    const ERROR_MSG = [
        400 => "リクエストパラメータが異常です。",
        403 => "アクセス権限がありません。",
        404 => "リソースが見つかりません。",
        405 => "リクエストされたHTTPメソッドは許可されていません。",
        408 => "リクエストがタイムアウトしました。",
        500 => "システムエラーが発生しました。\r\n" .
               "システム管理者へお問い合わせください。",
    ];

    /**
     * @var string
     */
    protected $message;

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * BaseErrorException constructor.
     *
     * @param int $statusCode ステータスコード
     * @param string $message 簡易エラーメッセージ
     */
    public function __construct(int $statusCode = 500, string $message = '')
    {
        $this->message = $message;
        $this->statusCode = $statusCode;
    }

    /**
     * @param string $message
     */
    public function setErrorMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        // エラーメッセージが未設定で
        // エラーメッセージ定義が存在するHTTPステータスコードの場合
        if (empty($this->message) &&
            array_key_exists($this->statusCode, self::ERROR_MSG)) {
            return self::ERROR_MSG[$this->statusCode];
        }

        return $this->message;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * エラーメッセージ定義済みのHTTPエラーか判定する
     *
     * @return true:定義済み、false:未定義
     */
    public function isDefindErrorCode(): bool
    {
        return array_key_exists($this->statusCode, self::ERROR_MSG);
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return new JsonResponse(
            $this->getBasicResponse(),
            $this->getStatusCode(),
            [], JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * エラーレスポンスの基本情報を取得する
     *
     * @return レスポンス情報
     *  string errorMsg エラーメッセージ
     */
    protected function getBasicResponse()
    {
        return ['errorMsg' => $this->getErrorMessage()];
    }
}