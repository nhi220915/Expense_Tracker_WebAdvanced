<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class TestCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:test {--user-id=1 : The user ID to test with} {--clear : Clear cache before testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test caching functionality and performance';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->option('user-id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return Command::FAILURE;
        }

        $this->info("Testing cache for user: {$user->name} (ID: {$user->id})");
        $this->newLine();

        if ($this->option('clear')) {
            $this->info('Clearing cache...');
            Cache::flush();
            $this->info('✓ Cache cleared');
            $this->newLine();
        }

        // Test basic cache operations
        $this->testBasicCaching();
        $this->newLine();

        // Test dashboard caching
        $this->testDashboardCaching($user);
        $this->newLine();

        // Test cache invalidation
        $this->testCacheInvalidation($user);
        $this->newLine();

        // Show cache statistics
        $this->showCacheStats();

        $this->info('✓ All cache tests completed successfully!');
        return Command::SUCCESS;
    }

    /**
     * Test basic caching functionality
     */
    protected function testBasicCaching(): void
    {
        $this->info('Testing basic cache operations...');

        // Test set and get
        Cache::put('test_key', 'test_value', 60);
        $value = Cache::get('test_key');

        if ($value === 'test_value') {
            $this->info('  ✓ Cache set/get working');
        } else {
            $this->error('  ✗ Cache set/get failed');
        }

        // Test remember
        $result = Cache::remember('test_remember', 60, function () {
            return 'remembered_value';
        });

        if ($result === 'remembered_value') {
            $this->info('  ✓ Cache remember working');
        } else {
            $this->error('  ✗ Cache remember failed');
        }

        // Test forget
        Cache::forget('test_key');
        if (!Cache::has('test_key')) {
            $this->info('  ✓ Cache forget working');
        } else {
            $this->error('  ✗ Cache forget failed');
        }

        // Clean up
        Cache::forget('test_remember');
    }

    /**
     * Test dashboard caching
     */
    protected function testDashboardCaching(User $user): void
    {
        $this->info('Testing dashboard caching...');

        $month = date('Y-m');

        // First call - should hit database
        $start = microtime(true);
        $data1 = CacheService::cacheDashboard($user, $month, function () {
            usleep(10000); // Simulate slow query (10ms)
            return ['totalIncome' => 1000, 'totalExpense' => 500];
        });
        $time1 = (microtime(true) - $start) * 1000;

        // Second call - should hit cache
        $start = microtime(true);
        $data2 = CacheService::cacheDashboard($user, $month, function () {
            usleep(10000); // This should not execute
            return ['totalIncome' => 1000, 'totalExpense' => 500];
        });
        $time2 = (microtime(true) - $start) * 1000;

        if ($data1 === $data2) {
            $this->info('  ✓ Dashboard cache returning correct data');
        } else {
            $this->error('  ✗ Dashboard cache data mismatch');
        }

        $this->info(sprintf('  ⏱ First call: %.2fms (database)', $time1));
        $this->info(sprintf(
            '  ⏱ Second call: %.2fms (cached) - %.1f%% faster',
            $time2,
            (($time1 - $time2) / $time1) * 100
        ));

        // Clean up
        CacheService::invalidateDashboardCache($user, $month);
    }

    /**
     * Test cache invalidation
     */
    protected function testCacheInvalidation(User $user): void
    {
        $this->info('Testing cache invalidation...');

        $month = date('Y-m');
        $key = CacheService::userKey($user, CacheService::PREFIX_DASHBOARD, $month);

        // Set cache
        Cache::put($key, ['test' => 'data'], 60);
        $this->info('  ✓ Cache set');

        // Verify cache exists
        if (Cache::has($key)) {
            $this->info('  ✓ Cache exists');
        } else {
            $this->error('  ✗ Cache should exist');
        }

        // Invalidate cache
        CacheService::invalidateDashboardCache($user, $month);

        // Verify cache was cleared
        if (!Cache::has($key)) {
            $this->info('  ✓ Cache invalidation working');
        } else {
            $this->error('  ✗ Cache should be cleared');
        }
    }

    /**
     * Show cache statistics
     */
    protected function showCacheStats(): void
    {
        $this->info('Cache configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Driver', config('cache.default')],
                ['Prefix', config('cache.prefix')],
                ['Short TTL', CacheService::CACHE_SHORT . 's (5 min)'],
                ['Medium TTL', CacheService::CACHE_MEDIUM . 's (1 hour)'],
                ['Long TTL', CacheService::CACHE_LONG . 's (24 hours)'],
            ]
        );
    }
}
