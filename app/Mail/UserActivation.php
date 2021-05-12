<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use App\Models\User;

class UserActivation extends Mailable
{
    use Queueable, SerializesModels;

    // 送信先ユーザ情報
    public $sendUser;

    /**
     * 新しいメッセージインスタンスの生成
     *
     * @param  \App\Models\User  $sendUser 送信先ユーザ情報
     * @return void
     */
    public function __construct(User $sendUser)
    {
        // 送信先ユーザ情報を取得
        $this->sendUser = $sendUser;
    }

    /**
     * メッセージを作成
     *
     * @return $this
     */
    public function build()
    {
        // 環境に応じてフロントエンドのURLを取得
        $env = 'local';
        if (env('APP_DEBUG', false)) {
            $env = 'product';
        }
        $url = config('const.frontend')[$env] .
               config('const.frontend')['resetPass'] .
               url('api/password/reset', $this->token);
        // 署名付きURL作成
        URL::temporarySignedRoute(
            'unsubscribe', now()->addMinutes(30), ['uuid' => $sendUser->uuid]
        );

        return $this->view('emails.admit');
    }
}
