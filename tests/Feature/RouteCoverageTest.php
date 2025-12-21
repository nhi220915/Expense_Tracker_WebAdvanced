<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ExpenseCategory;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteCoverageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test all main GET routes to ensure they are functional and cover controller index/edit methods.
     */
    public function test_main_routes_coverage(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);
        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id
        ]);

        $routes = [
            'dashboard',
            'expenses.index',
            'incomes.index',
            'profile.edit',
        ];

        foreach ($routes as $route) {
            $this->actingAs($user)
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_auth_routes_coverage(): void
    {
        $authRoutes = [
            'login',
            'register',
            'password.request',
        ];

        foreach ($authRoutes as $route) {
            $this->get(route($route))
                ->assertOk();
        }
    }

    public function test_guest_redirects(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }
}
