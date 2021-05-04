<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // 認証用のカスタムプロバイダを追加
        Auth::provider('crm_auth', function($app, array $config) {
            return new CrmAuthProvider($this->app['hash'], $config['model']);
        });
        // オーナー権限の場合のみ許可
        Gate::define('owner', function ($user) {
            return ($user->role == config('const.userAuth')['owner']);
        });
        // 管理者権限以上の場合に許可
        Gate::define('admin', function ($user) {
            return ($user->role >= config('const.userAuth')['admin']);
        });
        // バックオフィス権限以上の場合に許可
        Gate::define('back_office', function ($user) {
            return ($user->role >= config('const.userAuth')['backOffice']);
        });
    }
}
