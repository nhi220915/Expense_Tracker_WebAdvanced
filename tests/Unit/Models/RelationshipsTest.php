<?php

namespace Tests\Unit\Models;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\User;
use Tests\TestCase;

class RelationshipsTest extends TestCase
{
    public function test_user_relationships_exist(): void
    {
        $user = new User();

        $this->assertNotNull($user->expenses());
        $this->assertNotNull($user->incomes());
        $this->assertNotNull($user->budgets());
        $this->assertNotNull($user->expenseCategories());
    }

    public function test_expense_relationships_exist(): void
    {
        $expense = new Expense();
        $this->assertNotNull($expense->user());
        $this->assertNotNull($expense->category());
    }

    public function test_budget_relationships_exist(): void
    {
        $budget = new Budget();
        $this->assertNotNull($budget->user());
        $this->assertNotNull($budget->category());
    }

    public function test_expense_category_relationships_exist(): void
    {
        $cat = new ExpenseCategory();
        $this->assertNotNull($cat->user());
        $this->assertNotNull($cat->expenses());
    }

    public function test_income_relationship_exists(): void
    {
        $income = new Income();
        $this->assertNotNull($income->user());
    }
}

