<?php

namespace Tests\Unit\Models;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelAttributesTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_date_is_cast_to_date(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'date' => '2025-12-15',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $expense->date);
        $this->assertEquals('2025-12-15', $expense->date->format('Y-m-d'));
    }

    public function test_income_date_is_cast_to_date(): void
    {
        $user = User::factory()->create();

        $income = Income::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-12-01',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $income->date);
        $this->assertEquals('2025-12-01', $income->date->format('Y-m-d'));
    }

    public function test_expense_fillable_attributes(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        $expense = new Expense([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'amount' => 100.50,
            'date' => '2025-12-15',
            'note' => 'Test note',
        ]);

        $this->assertEquals($user->id, $expense->user_id);
        $this->assertEquals($category->id, $expense->expense_category_id);
        $this->assertEquals(100.50, $expense->amount);
        $this->assertEquals('Test note', $expense->note);
    }

    public function test_income_fillable_attributes(): void
    {
        $user = User::factory()->create();

        $income = new Income([
            'user_id' => $user->id,
            'amount' => 5000.00,
            'category' => 'Salary',
            'date' => '2025-12-01',
            'note' => 'December salary',
        ]);

        $this->assertEquals($user->id, $income->user_id);
        $this->assertEquals(5000.00, $income->amount);
        $this->assertEquals('Salary', $income->category);
        $this->assertEquals('December salary', $income->note);
    }

    public function test_budget_fillable_attributes(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        $budget = new Budget([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'limit' => 1000.00,
            'month' => 12,
            'year' => 2025,
        ]);

        $this->assertEquals($user->id, $budget->user_id);
        $this->assertEquals($category->id, $budget->expense_category_id);
        $this->assertEquals(1000.00, $budget->limit);
        $this->assertEquals(12, $budget->month);
        $this->assertEquals(2025, $budget->year);
    }

    public function test_expense_category_fillable_attributes(): void
    {
        $user = User::factory()->create();

        $category = new ExpenseCategory([
            'user_id' => $user->id,
            'name' => 'Food',
            'color' => '#ff5733',
        ]);

        $this->assertEquals($user->id, $category->user_id);
        $this->assertEquals('Food', $category->name);
        $this->assertEquals('#ff5733', $category->color);
    }

    public function test_user_has_hidden_password(): void
    {
        $user = User::factory()->create([
            'password' => 'secret123',
        ]);

        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
    }

    public function test_expense_belongs_to_user_and_category(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);
        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
        ]);

        $this->assertInstanceOf(User::class, $expense->user);
        $this->assertInstanceOf(ExpenseCategory::class, $expense->category);
        $this->assertEquals($user->id, $expense->user->id);
        $this->assertEquals($category->id, $expense->category->id);
    }

    public function test_user_can_have_multiple_expenses(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        Expense::factory()->count(3)->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
        ]);

        $this->assertCount(3, $user->expenses);
    }
}
