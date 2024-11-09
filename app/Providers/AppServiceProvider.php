<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::loadKeysFrom(storage_path());
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
        //
        $this->registerRouters();
    }

    private function registerRouters()
    {
        // Đăng ký route cho API với prefix 'api' và middleware 'api'
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));

        // Đăng ký route cho Web với middleware 'web'
        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }
}
