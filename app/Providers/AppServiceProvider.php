<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Events\QueryExecuted;
use App\Models\User;

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
        Gate::define('viewPulse', function (User $user) {
            // Cho phép TẤT CẢ người dùng đã đăng nhập vào xem
            return true;
        });

        DB::listen(function (QueryExecuted $query) {
            // Chỉ log nếu query chậm hơn 500ms
            if ($query->time > 500) {
                Log::warning('Phát hiện Slow Query!', [
                    'sql' => $query->sql,
                    'time' => $query->time . 'ms',
                    'connection' => $query->connectionName
                ]);

                // Nếu lỗi cực nghiêm trọng (> 2000ms), gửi Slack ngay lập tức
                if ($query->time > 2000) {
                    Log::critical("CẢNH BÁO KHẨN CẤP: Truy vấn siêu chậm ({$query->time}ms): {$query->sql}");
                }
            }
        });
    }
}