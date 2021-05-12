<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\User;

class VerificationController extends Controller
{
    use VerifiesEmails;

    public function __construct()
    {
        $this->middleware('throttle:6,1');
    }

    /*
     * メールアドレスの確認を行う
     */
    public function verify(Request $request, string $uuid)
    {
        // UUIDからユーザ情報を取得
        $user = User::pickUp($uuid, true);

        // メールアドレス確認日登録
        $user->markEmailAsVerified();
        event(new Verified($user));

        return new JsonResponse('Email Verified');
    }

    /*
     *
     */
    public function resend(Request $request)
    {
        $user = User::where('email', $request->get('email'))->get()->first();
        if (!$user) {
            return new JsonResponse('No Such User');
        }

        $user->sendEmailVerificationNotification();

        return new JsonResponse('Send Verify Email');
    }
}