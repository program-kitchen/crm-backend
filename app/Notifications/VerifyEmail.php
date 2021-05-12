<?php

namespace app\Notifications;

use App;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class VerifyEmail extends VerifyEmailBase
{
    protected function verificationUrl($user)
    {
        // 環境に応じてフロントエンドのURLを取得
        $env = 'local';
        if (env('APP_DEBUG', false)) {
            $env = 'product';
        }
        $prefix = config('const.frontend')[$env] .
                  config('const.frontend')['activation'] .
                  url('api/password/reset', $this->token);
        // 認証メールに記載するURLを生成
        $routeName = 'verification.verify';
        $temporarySignedURL = URL::temporarySignedRoute(
            $routeName, Carbon::now()->addMinutes(60), ['uuid' => $user->uuid]
        );

        return $prefix . urlencode($temporarySignedURL);
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
    
        return (new MailMessage)
            ->subject('【COACHTECH-CRM】本登録のお願い')
            ->view('emails.admit')
            ->line(Lang::get('Please click the button below to verify your email address.'))
            ->action(Lang::get('Verify Email Address'), $verificationUrl)
            ->line(Lang::get('If you did not create an account, no further action is required.'));
    }
}