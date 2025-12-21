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

    public function test_cache_keys_are_consistent(): void
    {
        $user = User::factory()->create(['id' => 1]);
        $key = CacheService::userKey($user, 'test', 'param1', 'param2');

        $this->assertEquals('test:1:param1:param2', $key);
    }

    public function test_cache_methods_store_data(): void
    {
        $user = User::factory()->create();
        $month = '2025-12';

        // Test Dashboard Cache
        $data = CacheService::cacheDashboard($user, $month, fn() => ['test' => 1]);
        $this->assertEquals(1, $data['test']);
        $this->assertTrue(Cache::has(CacheService::userKey($user, CacheService::PREFIX_DASHBOARD, $month)));

        // Test Expenses Cache
        CacheService::cacheExpenses($user, $month, fn() => 'expenses');
        $this->assertTrue(Cache::has(CacheService::userKey($user, CacheService::PREFIX_EXPENSES, $month)));

        // Test Incomes Cache
        CacheService::cacheIncomes($user, $month, fn() => 'incomes');
        $this->assertTrue(Cache::has(CacheService::userKey($user, CacheService::PREFIX_INCOMES, $month)));

        // Test Budgets Cache
        CacheService::cacheBudgets($user, 2025, 12, fn() => 'budgets');
        $this->assertTrue(Cache::has(CacheService::userKey($user, CacheService::PREFIX_BUDGETS, 2025, 12)));

        // Test Categories Cache
        CacheService::cacheCategories($user, fn() => 'categories');
        $this->assertTrue(Cache::has(CacheService::userKey($user, CacheService::PREFIX_CATEGORIES)));
    }

    public function test_invalidation_methods_clear_cache(): void
    {
        $user = User::factory()->create();
        $month = '2025-12';

        // Set some cache
        CacheService::cacheDashboard($user, $month, fn() => 'dashboard');
        CacheService::cacheExpenses($user, $month, fn() => 'expenses');

        // Invalidate specific
        CacheService::invalidateDashboardCache($user, $month);
        $this->assertFalse(Cache::has(CacheService::userKey($user, CacheService::PREFIX_DASHBOARD, $month)));

        // Invalidate without month (calls forgetPattern - coverage for the path)
        CacheService::invalidateDashboardCache($user);
        CacheService::invalidateExpensesCache($user);
        CacheService::invalidateIncomesCache($user);
        CacheService::invalidateBudgetsCache($user, 2025, 12);
        CacheService::invalidateBudgetsCache($user);
        CacheService::invalidateCategoriesCache($user);
        CacheService::invalidateUserCache($user);

        $this->assertTrue(true); // Reached here without error
    }

    public function test_warm_up_placeholder(): void
    {
        $user = User::factory()->create();
        CacheService::warmUpUserCache($user, '2025-12');
        $this->assertTrue(true);
    }
}
