<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_expenses_index(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('expenses.index'))
            ->assertOk();
    }

    public function test_user_can_create_expense(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $category = ExpenseCategory::factory()->create([
            'user_id' => $user->id,
            'name' => 'Food',
        ]);

        $payload = [
            'amount' => 12.50,
            'expense_category_id' => $category->id,
            'date' => '2025-12-14',
            'note' => 'Highland Coffee',
        ];

        $this->actingAs($user)
            ->post(route('expenses.store'), $payload)
            ->assertRedirect(route('expenses.index', ['month' => '2025-12'], absolute: false));

        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'amount' => 12.50,
            'note' => 'Highland Coffee',
        ]);
    }

    public function test_user_can_update_own_expense(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);
        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'amount' => 10.00,
            'date' => '2025-12-10',
        ]);

        $newCategory = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('expenses.update', $expense), [
                'amount' => 99.99,
                'expense_category_id' => $newCategory->id,
                'date' => '2025-12-11',
                'note' => 'Updated note',
            ])
            ->assertRedirect(route('expenses.index', ['month' => '2025-12'], absolute: false));

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'user_id' => $user->id,
            'expense_category_id' => $newCategory->id,
            'amount' => 99.99,
            'note' => 'Updated note',
        ]);
    }

    public function test_user_cannot_update_other_users_expense(): void
    {
        $owner = User::factory()->create();
        /** @var User $other */
        $other = User::factory()->create();

        $category = ExpenseCategory::factory()->create(['user_id' => $owner->id]);
        $expense = Expense::factory()->create([
            'user_id' => $owner->id,
            'expense_category_id' => $category->id,
        ]);

        $this->actingAs($other)
            ->put(route('expenses.update', $expense), [
                'amount' => 1,
                'expense_category_id' => $category->id,
                'date' => '2025-12-11',
            ])
            ->assertForbidden();
    }

    public function test_user_can_delete_own_expense(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);
        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'date' => '2025-12-10',
        ]);

        $this->actingAs($user)
            ->delete(route('expenses.destroy', $expense))
            ->assertRedirect(route('expenses.index', ['month' => '2025-12'], absolute: false));

        $this->assertDatabaseMissing('expenses', [
            'id' => $expense->id,
        ]);
    }
    public function test_user_cannot_create_expense_with_invalid_data(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('expenses.store'), [
                'amount' => 'not-a-number',
                'expense_category_id' => 9999, // Non-existent
                'date' => 'invalid-date',
            ])
            ->assertSessionHasErrors(['amount', 'expense_category_id', 'date']);
    }

    public function test_user_can_view_expenses_filtered_by_month(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        // Expense in Nov
        Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'date' => '2025-11-15'
        ]);

        // Expense in Dec
        Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'date' => '2025-12-15'
        ]);

        $this->actingAs($user)
            ->get(route('expenses.index', ['month' => '2025-11']))
            ->assertOk()
            ->assertSee('2025-11-15')
            ->assertDontSee('2025-12-15');
    }
}