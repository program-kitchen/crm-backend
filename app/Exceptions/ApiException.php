<?php

namespace App\Exceptions;

use App\Exceptions\BaseException;

/*
 * API用例外クラス
 * APIの処理中にエラーが発生した場合はApiExceptionをthrowする。
 */
class ApiException extends BaseException
{
    /**
     * BaseErrorException constructor.
     *
     * @param int $statusCode ステータスコード
     * @param string $message エラーメッセージ
     */
    public function __construct(int $statusCode, string $message) {
        $this->message = $message;
        $this->statusCode = $statusCode;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        // この例外クラスではエラーメッセージが必須なので、そのまま返す。
        return $this->message;
    }
}