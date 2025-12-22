<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('dashboard.index');
        $response->assertViewHas(['totalIncome', 'totalExpense', 'expenseByCategory', 'incomeByCategory', 'budgetProgress']);
    }

    public function test_dashboard_displays_correct_stats_for_selected_month(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id, 'name' => 'Food']);

        // Data for current month (assume Dec 2025 for this test context or use dynamic dates)
        // Ideally we control the month via the request, so let's pick 2025-10
        $targetMonth = '2025-10';
        $otherMonth = '2025-09';

        // 1. Expense in target month
        Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'amount' => 100,
            'date' => $targetMonth . '-15',
        ]);

        // 2. Expense in OTHER month (should not be counted)
        Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'amount' => 500,
            'date' => $otherMonth . '-15',
        ]);

        // 3. Income in target month
        Income::factory()->create([
            'user_id' => $user->id,
            'amount' => 2000,
            'date' => $targetMonth . '-01',
            'category' => 'Salary'
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', ['month' => $targetMonth]));

        $response->assertOk();
        $response->assertViewHas('totalExpense', 100);
        $response->assertViewHas('totalIncome', 2000);

        // Verify expense by category
        $expenseByCategory = $response->viewData('expenseByCategory');
        $this->assertArrayHasKey('Food', $expenseByCategory);
        $this->assertEquals(100, $expenseByCategory['Food']);
    }

    public function test_dashboard_shows_budget_progress(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id, 'name' => 'Transport']);

        $targetMonth = '2025-10';

        // Set budget
        Budget::create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'limit' => 500,
            'month' => 10,
            'year' => 2025,
        ]);

        // Spend some
        Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'amount' => 150,
            'date' => $targetMonth . '-05',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', ['month' => $targetMonth]));

        $response->assertOk();
        $budgetProgress = $response->viewData('budgetProgress');

        $this->assertCount(1, $budgetProgress);
        $this->assertEquals('Transport', $budgetProgress[0]['category']);
        $this->assertEquals(500, $budgetProgress[0]['limit']);
        $this->assertEquals(150, $budgetProgress[0]['spent']);
    }
}
