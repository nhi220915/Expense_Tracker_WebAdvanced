<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_budget(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        
        $category = ExpenseCategory::factory()->create([
            'user_id' => $user->id,
            'name' => 'Food',
        ]);

        $payload = [
            'expense_category_id' => $category->id,
            'limit' => 1000,
            'month' => 12,
            'year' => 2025,
        ];

        $this->actingAs($user)
            ->post(route('budgets.store'), $payload)
            ->assertRedirect(route('expenses.index', ['month' => '2025-12']));

        $this->assertDatabaseHas('budgets', [
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'limit' => 1000,
            'month' => 12,
            'year' => 2025,
        ]);
    }

    public function test_user_can_update_budget_limit(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);
        
        Budget::create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'limit' => 500,
            'month' => 12,
            'year' => 2025,
        ]);

        $payload = [
            'overall_limit' => 2000,
            'month' => '2025-12',
        ];

        $this->actingAs($user)
            ->put(route('budgets.update-limit'), $payload)
            ->assertRedirect(route('expenses.index', ['month' => '2025-12']));

        $this->assertDatabaseHas('budgets', [
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'limit' => 2000,
            'month' => 12,
            'year' => 2025,
        ]);
    }

    public function test_user_can_update_budget_allocation(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        
        $category1 = ExpenseCategory::factory()->create(['user_id' => $user->id, 'name' => 'Food']);
        $category2 = ExpenseCategory::factory()->create(['user_id' => $user->id, 'name' => 'Transport']);

        Budget::create(['user_id' => $user->id, 'expense_category_id' => $category1->id, 'limit' => 1000, 'month' => 12, 'year' => 2025]);
        Budget::create(['user_id' => $user->id, 'expense_category_id' => $category2->id, 'limit' => 1000, 'month' => 12, 'year' => 2025]);

        $payload = [
            'percentages' => [
                'Food' => 60,
                'Transport' => 40,
            ],
            'month' => '2025-12',
        ];

        $this->actingAs($user)
            ->put(route('budgets.update-allocation'), $payload)
            ->assertRedirect(route('expenses.index', ['month' => '2025-12']));

        $this->assertDatabaseHas('budgets', [
            'user_id' => $user->id,
            'expense_category_id' => $category1->id,
            'limit' => 1200,
        ]);

        $this->assertDatabaseHas('budgets', [
            'user_id' => $user->id,
            'expense_category_id' => $category2->id,
            'limit' => 800,
        ]);
    }
}