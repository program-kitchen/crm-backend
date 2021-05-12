<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\JsonResponse;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;
    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    // 追加
    public function reset(Request $request)
    {
        $validate = $this->validator($request->all());

        if ($validate->fails()) {
            return new JsonResponse($validate->errors());
        }
        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        return $response == Password::PASSWORD_RESET
                    ? $this->sendResetResponse($request, $response)
                    : $this->sendResetFailedResponse($request, $response);
    }

    protected function resetPassword($user, $password)
    {
        $user->forceFill([
            'password' => bcrypt($password),
            'remember_token' => Str::random(60),
        ])->save();
    }

    protected function sendResetResponse(Request $request, $response)
    {
        return new JsonResponse('Password Reset');
    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        return new JsonResponse($response);
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|pass_valid',
        ]);
    }
}