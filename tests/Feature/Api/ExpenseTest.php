<?php

namespace Tests\Feature\Api;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_expenses(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        Expense::factory()->count(3)->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/expenses');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_expense(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        $payload = [
            'amount' => 100.50,
            'expense_category_id' => $category->id,
            'date' => '2025-12-01',
            'note' => 'API Test Expense',
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/expenses', $payload);

        $response->assertCreated();

        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'amount' => 100.50,
        ]);
    }

    public function test_user_can_update_expense(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);
        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'amount' => 50,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/expenses/{$expense->id}", [
                'amount' => 75.00,
                'expense_category_id' => $category->id,
                'date' => '2025-12-02',
                'note' => 'Updated Note'
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'amount' => 75.00,
        ]);
    }

    public function test_user_cannot_update_others_expense(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $otherUser->id]);
        $expense = Expense::factory()->create([
            'user_id' => $otherUser->id,
            'expense_category_id' => $category->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/expenses/{$expense->id}", [
                'amount' => 999,
                'expense_category_id' => $category->id, // This category belongs to otherUser, but regardless, expense belongs to otherUser
                'date' => '2025-12-02',
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_expense(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);
        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/expenses/{$expense->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }
}
