<?php

namespace App\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Exception;
use Throwable;

/*
 * エラーハンドリングクラス
 * Laravel全体のエラー制御を行う。
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        // バリデーションでエラーが発生した場合
        if ($e instanceof ValidationException) {
            return $this->toResponse(
                $request, $e->status, json_encode($e->errors(), JSON_UNESCAPED_UNICODE)
            );
        }
        // それ以外のエラーが発生した場合

        // エラーログ出力
        \Log::error(
            'Error occured : request_url=' . $request->fullUrl() . "\r\n"
            "Exception :\r\n" . $e
        );

        // Responsableインターフェースを継承したクラスはここでレスポンスを返す
        if ($e instanceof Responsable) {
            return $e->toResponse($request);
        }

        // HTTP系例外が発生した場合
        if ($this->isHttpException($e)) {
            return $this->toResponse(
                $request, $e->getStatusCode(), $e->getMessage()
            );
        }

        if (env('APP_DEBUG', false)) {
            // デバッグ環境の場合は標準のエラー出力
            return parent::render($request, $e);
        } else {
            // 本番環境の場合は Internal Server Error を出力
            return $this->toResponse($request, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * フロント側へエラーレスポンスを返す
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $statusCode HTTPステータスコード
     * @param  string $message エラーメッセージ
     * @return \Illuminate\Http\Response レスポンス情報
     */
    protected function toResponse($request, int $statusCode, string $message = '')
    {
         return (new BaseException($statusCode, $message))
            ->toResponse($request);
    }
}
