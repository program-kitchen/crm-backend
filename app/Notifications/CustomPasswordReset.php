<?php

namespace App\Notifications;

use App;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CustomPasswordReset extends Notification
{
    use Queueable;
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // 環境に応じてフロントエンドのURLを取得
        $env = 'local';
        if (env('APP_DEBUG', false)) {
            $env = 'product';
        }
        $url = config('const.frontend')[$env] .
               config('const.frontend')['resetPass'] .
               url('api/password/reset', $this->token);

        return (new MailMessage)
            ->subject('【COACHTECH-CRM】パスワードリセットのお願い')
            ->view('emails.resetpass')
            ->action('リセットパスワード', $url);
    }
}