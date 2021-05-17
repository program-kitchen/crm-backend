<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/*
 * パスワードリセットメール用のメッセージクラス
 */
class ResetPass extends Mailable
{
    use Queueable, SerializesModels;

    // 送信ユーザ名
    public $userName;
    // パスワードリセット用URL
    public $url;

    /**
     * パスワードリセットメール用のメッセージクラスを生成する
     *
     * @param  string $userName 送信ユーザ名
     * @param  string $url      パスワードリセット用URL
     * @return void
     */
    public function __construct(string $userName, string $url)
    {
        $this->userName = $userName;
        $this->url = $url;
    }

    /**
     * メール本文を作成する
     *
     * @return $this
     */
    public function build()
    {
        return $this->text('emails.resetpass')
                    ->subject('【COACHTECH-CRM】パスワード変更のお願い');
    }
}
