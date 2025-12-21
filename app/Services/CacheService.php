<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\User;

class CacheService
{
    /**
     * Cache duration constants (in seconds)
     */
    const CACHE_SHORT = 300;      // 5 minutes
    const CACHE_MEDIUM = 3600;    // 1 hour
    const CACHE_LONG = 86400;     // 24 hours

    /**
     * Cache key prefixes
     */
    const PREFIX_USER = 'user';
    const PREFIX_DASHBOARD = 'dashboard';
    const PREFIX_EXPENSES = 'expenses';
    const PREFIX_INCOMES = 'incomes';
    const PREFIX_BUDGETS = 'budgets';
    const PREFIX_CATEGORIES = 'categories';

    /**
     * Generate a cache key for user-specific data
     */
    public static function userKey(User $user, string $prefix, ...$params): string
    {
        $paramString = implode(':', array_filter($params));
        return sprintf('%s:%d:%s', $prefix, $user->id, $paramString);
    }

    /**
     * Cache dashboard data
     */
    public static function cacheDashboard(User $user, string $month, \Closure $callback)
    {
        $key = self::userKey($user, self::PREFIX_DASHBOARD, $month);
        return Cache::remember($key, self::CACHE_SHORT, $callback);
    }

    /**
     * Cache expenses list
     */
    public static function cacheExpenses(User $user, string $month, \Closure $callback)
    {
        $key = self::userKey($user, self::PREFIX_EXPENSES, $month);
        return Cache::remember($key, self::CACHE_SHORT, $callback);
    }

    /**
     * Cache incomes list
     */
    public static function cacheIncomes(User $user, string $month, \Closure $callback)
    {
        $key = self::userKey($user, self::PREFIX_INCOMES, $month);
        return Cache::remember($key, self::CACHE_SHORT, $callback);
    }

    /**
     * Cache budgets list
     */
    public static function cacheBudgets(User $user, int $year, int $month, \Closure $callback)
    {
        $key = self::userKey($user, self::PREFIX_BUDGETS, $year, $month);
        return Cache::remember($key, self::CACHE_MEDIUM, $callback);
    }

    /**
     * Cache expense categories
     */
    public static function cacheCategories(User $user, \Closure $callback)
    {
        $key = self::userKey($user, self::PREFIX_CATEGORIES);
        return Cache::remember($key, self::CACHE_LONG, $callback);
    }

    /**
     * Invalidate all user-related caches
     */
    public static function invalidateUserCache(User $user): void
    {
        Cache::tags([self::PREFIX_USER . ':' . $user->id])->flush();
    }

    /**
     * Invalidate dashboard cache for a user
     */
    public static function invalidateDashboardCache(User $user, ?string $month = null): void
    {
        if ($month) {
            $key = self::userKey($user, self::PREFIX_DASHBOARD, $month);
            Cache::forget($key);
        } else {
            // Invalidate all dashboard caches for this user
            $pattern = self::userKey($user, self::PREFIX_DASHBOARD, '*');
            self::forgetPattern($pattern);
        }
    }

    /**
     * Invalidate expenses cache for a user
     */
    public static function invalidateExpensesCache(User $user, ?string $month = null): void
    {
        if ($month) {
            $key = self::userKey($user, self::PREFIX_EXPENSES, $month);
            Cache::forget($key);
        } else {
            $pattern = self::userKey($user, self::PREFIX_EXPENSES, '*');
            self::forgetPattern($pattern);
        }

        // Also invalidate dashboard since it depends on expenses
        self::invalidateDashboardCache($user, $month);
    }

    /**
     * Invalidate incomes cache for a user
     */
    public static function invalidateIncomesCache(User $user, ?string $month = null): void
    {
        if ($month) {
            $key = self::userKey($user, self::PREFIX_INCOMES, $month);
            Cache::forget($key);
        } else {
            $pattern = self::userKey($user, self::PREFIX_INCOMES, '*');
            self::forgetPattern($pattern);
        }

        // Also invalidate dashboard since it depends on incomes
        self::invalidateDashboardCache($user, $month);
    }

    /**
     * Invalidate budgets cache for a user
     */
    public static function invalidateBudgetsCache(User $user, ?int $year = null, ?int $month = null): void
    {
        if ($year && $month) {
            $key = self::userKey($user, self::PREFIX_BUDGETS, $year, $month);
            Cache::forget($key);

            $monthStr = sprintf('%04d-%02d', $year, $month);
            self::invalidateDashboardCache($user, $monthStr);
        } else {
            $pattern = self::userKey($user, self::PREFIX_BUDGETS, '*');
            self::forgetPattern($pattern);
            self::invalidateDashboardCache($user);
        }
    }

    /**
     * Invalidate categories cache for a user
     */
    public static function invalidateCategoriesCache(User $user): void
    {
        $key = self::userKey($user, self::PREFIX_CATEGORIES);
        Cache::forget($key);

        // Also invalidate related caches
        self::invalidateExpensesCache($user);
        self::invalidateDashboardCache($user);
    }

    /**
     * Helper method to forget cache keys matching a pattern
     * Note: This is a simple implementation. For production with Redis, 
     * consider using Redis SCAN command for better performance.
     */
    private static function forgetPattern(string $pattern): void
    {
        // For database/file cache, we need to clear all matching keys
        // This is a simplified version - in production, consider using cache tags
        $cacheStore = config('cache.default');

        if ($cacheStore === 'redis') {
            // For Redis, we could use SCAN command
            // For now, we'll just document that cache tags should be used
        }

        // For other stores, individual keys need to be tracked and cleared
        // This is why cache tags are recommended for complex invalidation scenarios
    }

    /**
     * Warm up cache for a user
     * This can be called after login or data updates to pre-populate cache
     */
    public static function warmUpUserCache(User $user, string $month): void
    {
        // This can be run asynchronously in a queue job
        // For now, it's just a placeholder for the concept
    }
}
