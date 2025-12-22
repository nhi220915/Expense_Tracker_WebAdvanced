<?php

namespace Tests\Unit\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TestCacheCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_runs_successfully_for_existing_user(): void
    {
        $user = User::factory()->create();

        $this->artisan('cache:test', ['--user-id' => $user->id])
            ->assertSuccessful()
            ->expectsOutputToContain("Testing cache for user: {$user->name}")
            ->expectsOutputToContain('✓ All cache tests completed successfully!');
    }

    public function test_it_fails_for_non_existent_user(): void
    {
        $this->artisan('cache:test', ['--user-id' => 99999])
            ->assertFailed()
            ->expectsOutput('User with ID 99999 not found!');
    }

    public function test_it_clears_cache_when_requested(): void
    {
        Cache::put('foo', 'bar', 60);
        $user = User::factory()->create();

        $this->artisan('cache:test', ['--user-id' => $user->id, '--clear' => true])
            ->assertSuccessful()
            ->expectsOutput('✓ Cache cleared');

        $this->assertFalse(Cache::has('foo'));
    }
}
