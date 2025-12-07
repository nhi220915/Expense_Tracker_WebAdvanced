<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route; // <-- THÊM DÒNG NÀY

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
        // Redirect to dashboard after login
        // Dòng này (Route::middleware) giờ sẽ hoạt động
        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }
}