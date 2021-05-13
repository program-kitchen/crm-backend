<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/*
 * ユーザ認証メール用のメッセージクラス
 */
class UserActivation extends Mailable
{
    use Queueable, SerializesModels;

    // 送信ユーザ名
    public $userName;
    // ユーザ認証用URL
    public $url;

    /**
     * ユーザ認証メール用のメッセージクラスを生成する
     *
     * @param  string $userName 送信ユーザ名
     * @param  string $url      ユーザ認証用URL
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
        return $this->text('emails.admit')
                    ->subject('【COACHTECH-CRM】本登録のお願い');
    }
}
