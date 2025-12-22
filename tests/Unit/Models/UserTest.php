<?php

namespace Tests\Unit\Models;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_expenses(): void
    {
        $user = User::factory()->create();
        $expense = Expense::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->expenses()->exists());
        $this->assertInstanceOf(Expense::class, $user->expenses->first());
    }

    public function test_user_has_incomes(): void
    {
        $user = User::factory()->create();
        $income = Income::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->incomes()->exists());
        $this->assertInstanceOf(Income::class, $user->incomes->first());
    }

    public function test_user_has_budgets(): void
    {
        $user = User::factory()->create();
        // Create category first as Budget factory might rely on it or we explicitcreate it
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id
        ]);

        $this->assertTrue($user->budgets()->exists());
        $this->assertInstanceOf(Budget::class, $user->budgets->first());
    }

    public function test_user_has_expense_categories(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->expenseCategories()->exists());
        $this->assertInstanceOf(ExpenseCategory::class, $user->expenseCategories->first());
    }

    public function test_user_password_is_hashed_and_hidden(): void
    {
        $user = User::factory()->create(['password' => 'secret']);

        $this->assertNotEquals('secret', $user->password);
        $this->assertArrayNotHasKey('password', $user->toArray());
    }

    public function test_user_email_verified_at_is_datetime(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->email_verified_at);
    }
}
