<?php

namespace Tests\Feature\Api;

use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_user_can_list_budgets(): void
    {
        $user = User::factory()->create();
        $categories = ExpenseCategory::factory()->count(3)->create(['user_id' => $user->id]);

        foreach ($categories as $category) {
            Budget::factory()->create([
                'user_id' => $user->id,
                'expense_category_id' => $category->id,
                'year' => 2025,
                'month' => 12,
            ]);
        }

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/budgets?month=2025-12');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_budget(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        $payload = [
            'expense_category_id' => $category->id,
            'limit' => 500.00,
            'month' => 12,
            'year' => 2025,
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/budgets', $payload);

        $response->assertCreated();

        $this->assertDatabaseHas('budgets', [
            'user_id' => $user->id,
            'limit' => 500.00,
            'month' => 12,
            'year' => 2025,
        ]);
    }

    public function test_user_can_update_overall_limit(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);
        Budget::create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'limit' => 100,
            'month' => 12,
            'year' => 2025,
        ]);

        $payload = [
            'overall_limit' => 1000,
            'month' => '2025-12',
        ];

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/budgets/update-limit', $payload);

        $response->assertOk();

        $this->assertDatabaseHas('budgets', [
            'user_id' => $user->id,
            'limit' => 1000,
        ]);
    }

    public function test_user_can_update_budget_allocation(): void
    {
        $user = User::factory()->create();
        $category1 = ExpenseCategory::factory()->create(['user_id' => $user->id, 'name' => 'Food']);
        $category2 = ExpenseCategory::factory()->create(['user_id' => $user->id, 'name' => 'Transport']);

        // Create initial budgets summing to 1000
        Budget::create(['user_id' => $user->id, 'expense_category_id' => $category1->id, 'limit' => 500, 'month' => 12, 'year' => 2025]);
        Budget::create(['user_id' => $user->id, 'expense_category_id' => $category2->id, 'limit' => 500, 'month' => 12, 'year' => 2025]);

        $payload = [
            'percentages' => [
                'Food' => 70,
                'Transport' => 30,
            ],
            'month' => '2025-12',
        ];

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/budgets/update-allocation', $payload);
        $response->assertOk();

        $this->assertDatabaseHas('budgets', [
            'expense_category_id' => $category1->id,
            'limit' => 700,
        ]);

        $this->assertDatabaseHas('budgets', [
            'expense_category_id' => $category2->id,
            'limit' => 300,
        ]);
    }

    public function test_user_can_delete_budget(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/budgets/{$budget->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
    }
}
