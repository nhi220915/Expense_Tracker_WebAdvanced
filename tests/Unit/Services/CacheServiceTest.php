<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\CacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_key_generation_is_correct(): void
    {
        $user = User::factory()->create(['id' => 123]);

        // Test basic key
        $key = CacheService::userKey($user, 'test');
        $this->assertEquals('test:123:', $key . '');
        // Wait, the implementation is sprintf('%s:%d:%s', $prefix, $user->id, $paramString);
        // If params are empty, it might have trailing colon or not depending on implode logic.
        // Let's check implementation: implode(':', array_filter($params))
        // If params empty, imploded string is empty. 
        // Result: "prefix:123:" -> because of trailing %s which is empty string?

        // Actually let's just test return value against expected string
        // userKey($user, 'prefix', 'param1', 'param2')

        $key2 = CacheService::userKey($user, 'dashboard', '2025-12');
        $this->assertEquals('dashboard:123:2025-12', $key2);
    }

    public function test_cache_dashboard_stores_data(): void
    {
        $user = User::factory()->create();
        $month = '2025-12';

        Cache::shouldReceive('remember')
            ->once()
            ->withArgs(function ($key, $ttl, $callback) use ($user, $month) {
                return $key === "dashboard:{$user->id}:{$month}"
                    && $ttl === CacheService::CACHE_SHORT;
            })
            ->andReturn(['total' => 1000]);

        $result = CacheService::cacheDashboard($user, $month, function () {
            return ['total' => 1000];
        });

        $this->assertEquals(['total' => 1000], $result);
    }

    public function test_invalidate_user_cache_clears_tags_or_keys(): void
    {
        $user = User::factory()->create();

        // Since we might be using file driver which doesn't support tags in some versions or config,
        // and CacheService has specific logic.
        // The service uses Cache::tags([...])->flush() for invalidateUserCache.
        // This requires a cache driver that supports tags (Redis/Memcached). 
        // The test env typically uses 'array' which supports tags.

        Cache::shouldReceive('tags')
            ->once()
            ->with(['user:' . $user->id])
            ->andReturnSelf();

        Cache::shouldReceive('flush')
            ->once();

        CacheService::invalidateUserCache($user);
    }

    public function test_invalidate_expenses_cache_clears_specific_key(): void
    {
        $user = User::factory()->create();
        $month = '2025-12';
        $key = "expenses:{$user->id}:{$month}";
        $dashboardKey = "dashboard:{$user->id}:{$month}";

        Cache::shouldReceive('forget')->with($key)->once();
        Cache::shouldReceive('forget')->with($dashboardKey)->once();

        CacheService::invalidateExpensesCache($user, $month);
    }
}
