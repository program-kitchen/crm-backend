<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;

class ValidatorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // emailの標準フォーマットチェックをカスタム
        Validator::extend('email', function ($attribute, $value, $parameters, $validator) {
            return !preg_match(config('const.regex')['email'], $value);
        });

        // パスワードの無効文字チェック追加 
        Validator::extend('pass_valid', function ($attribute, $value, $parameters, $validator) {
            return !preg_match(config('const.regex')['password']['invalid'], $value);
        });

        // パスワードの組み合わせチェック追加
        Validator::extend('pass_format', function ($attribute, $value, $parameters, $validator) {
            return preg_match(config('const.regex')['password']['format'], $value);
        });

        // UUIDのフォーマットチェックを追加
        Validator::extend('uuid', function ($attribute, $value, $parameters, $validator) {
            return !preg_match(config('const.regex')['uuid'], $value);
        });
    }
}
