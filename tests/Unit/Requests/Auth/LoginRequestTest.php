<?php

namespace Tests\Unit\Requests\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorize_returns_true(): void
    {
        $request = new LoginRequest();

        $this->assertTrue($request->authorize());
    }

    public function test_rules_requires_email_and_password(): void
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertContains('required', $rules['email']);
        $this->assertContains('email', $rules['email']);
        $this->assertContains('required', $rules['password']);
    }

    public function test_authenticate_succeeds_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $request->authenticate();

        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_authenticate_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->expectException(ValidationException::class);
        $request->authenticate();
    }

    public function test_throttle_key_uses_email_and_ip(): void
    {
        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'Test@Example.com',
        ]);

        $throttleKey = $request->throttleKey();

        $this->assertStringContainsString('test@example.com', $throttleKey);
        $this->assertStringContainsString('127.0.0.1', $throttleKey);
    }

    public function test_rate_limiting_triggers_after_too_many_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        // Simulate 5 failed attempts  
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($request->throttleKey());
        }

        $this->expectException(ValidationException::class);
        $request->ensureIsNotRateLimited();
    }

    public function test_rate_limiting_clears_on_successful_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Hit rate limiter a few times
        RateLimiter::hit($request->throttleKey(), 3);

        $request->authenticate();

        // Should be cleared after successful login
        $this->assertEquals(0, RateLimiter::attempts($request->throttleKey()));
    }
}
