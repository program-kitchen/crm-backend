<?php

namespace App\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
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
        Response::HTTP_BAD_REQUEST           => "リクエストパラメータが異常です。",
        Response::HTTP_FORBIDDEN             => "アクセス権限がありません。",
        Response::HTTP_NOT_FOUND             => "リソースが見つかりません。",
        Response::HTTP_METHOD_NOT_ALLOWED    => "リクエストされたHTTPメソッドは許可されていません。",
        Response::HTTP_REQUEST_TIMEOUT       => "リクエストがタイムアウトしました。",
        Response::HTTP_INTERNAL_SERVER_ERROR =>
            "システムエラーが発生しました。\r\n" .
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
     * @param string $message エラーメッセージ
     */
    public function __construct(
        int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR,
        string $message = ''
    ) {
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