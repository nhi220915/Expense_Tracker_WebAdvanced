<?php

namespace Tests\Unit\Services;

use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Services\BudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BudgetService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BudgetService();
    }

    public function test_list_for_user_by_month_returns_budgets_for_given_month(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        Budget::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'year' => 2025,
            'month' => 12,
        ]);
        Budget::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'year' => 2025,
            'month' => 11,
        ]);

        $budgets = $this->service->listForUserByMonth($user, 2025, 12);

        $this->assertCount(1, $budgets);
        $this->assertEquals(12, $budgets->first()->month);
    }

    public function test_create_or_update_creates_new_budget(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        $data = [
            'expense_category_id' => $category->id,
            'limit' => 500.00,
            'month' => 12,
            'year' => 2025,
        ];

        $budget = $this->service->createOrUpdate($user, $data);

        $this->assertInstanceOf(Budget::class, $budget);
        $this->assertEquals(500.00, $budget->limit);
        $this->assertEquals($user->id, $budget->user_id);
        $this->assertDatabaseHas('budgets', [
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'limit' => 500.00,
        ]);
    }

    public function test_create_or_update_updates_existing_budget(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);
        $existingBudget = Budget::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'limit' => 300.00,
            'year' => 2025,
            'month' => 12,
        ]);

        $data = [
            'expense_category_id' => $category->id,
            'limit' => 600.00,
            'month' => 12,
            'year' => 2025,
        ];

        $budget = $this->service->createOrUpdate($user, $data);

        $this->assertEquals($existingBudget->id, $budget->id);
        $this->assertEquals(600.00, $budget->limit);
        $this->assertDatabaseHas('budgets', [
            'id' => $existingBudget->id,
            'limit' => 600.00,
        ]);
    }

    public function test_create_or_update_throws_403_for_invalid_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherCategory = ExpenseCategory::factory()->create(['user_id' => $otherUser->id]);

        $data = [
            'expense_category_id' => $otherCategory->id,
            'limit' => 500.00,
            'month' => 12,
            'year' => 2025,
        ];

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Invalid expense category');

        $this->service->createOrUpdate($user, $data);
    }

    public function test_update_overall_limit_creates_budgets_for_all_categories(): void
    {
        $user = User::factory()->create();
        $category1 = ExpenseCategory::factory()->create(['user_id' => $user->id, 'name' => 'Food']);
        $category2 = ExpenseCategory::factory()->create(['user_id' => $user->id, 'name' => 'Transport']);

        $result = $this->service->updateOverallLimit($user, 1000.00, 2025, 12);

        $this->assertTrue($result);
        $budgets = Budget::where('user_id', $user->id)
            ->where('year', 2025)
            ->where('month', 12)
            ->get();

        $this->assertCount(2, $budgets);
        $this->assertEquals(1000.00, $budgets->sum('limit'));
    }

    public function test_update_overall_limit_updates_existing_budgets_proportionally(): void
    {
        $user = User::factory()->create();
        $category1 = ExpenseCategory::factory()->create(['user_id' => $user->id]);
        $category2 = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        Budget::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category1->id,
            'limit' => 300.00,
            'year' => 2025,
            'month' => 12,
        ]);
        Budget::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category2->id,
            'limit' => 700.00,
            'year' => 2025,
            'month' => 12,
        ]);

        $result = $this->service->updateOverallLimit($user, 2000.00, 2025, 12);

        $this->assertTrue($result);
        $budgets = Budget::where('user_id', $user->id)
            ->where('year', 2025)
            ->where('month', 12)
            ->get();

        $this->assertEquals(2000.00, $budgets->sum('limit'));
    }

    public function test_update_overall_limit_throws_exception_when_no_categories_exist(): void
    {
        $user = User::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No expense categories found');

        $this->service->updateOverallLimit($user, 1000.00, 2025, 12);
    }

    public function test_update_allocation_updates_budgets_based_on_percentages(): void
    {
        $user = User::factory()->create();
        $category1 = ExpenseCategory::factory()->create(['user_id' => $user->id, 'name' => 'Food']);
        $category2 = ExpenseCategory::factory()->create(['user_id' => $user->id, 'name' => 'Transport']);

        Budget::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category1->id,
            'limit' => 500.00,
            'year' => 2025,
            'month' => 12,
        ]);
        Budget::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category2->id,
            'limit' => 500.00,
            'year' => 2025,
            'month' => 12,
        ]);

        $percentages = [
            'Food' => 60,
            'Transport' => 40,
        ];

        $result = $this->service->updateAllocation($user, $percentages, 2025, 12);

        $this->assertTrue($result);
        $foodBudget = Budget::where('expense_category_id', $category1->id)->first();
        $transportBudget = Budget::where('expense_category_id', $category2->id)->first();

        $this->assertEquals(600.00, $foodBudget->limit);
        $this->assertEquals(400.00, $transportBudget->limit);
    }

    public function test_update_allocation_throws_exception_when_total_not_100(): void
    {
        $user = User::factory()->create();

        $percentages = [
            'Food' => 50,
            'Transport' => 30,
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Total allocation must equal 100%');

        $this->service->updateAllocation($user, $percentages, 2025, 12);
    }

    public function test_validation_rules_are_correct(): void
    {
        $rules = BudgetService::validationRules();

        $this->assertArrayHasKey('expense_category_id', $rules);
        $this->assertArrayHasKey('limit', $rules);
        $this->assertArrayHasKey('month', $rules);
        $this->assertArrayHasKey('year', $rules);
    }

    public function test_update_limit_validation_rules_are_correct(): void
    {
        $rules = BudgetService::updateLimitValidationRules();

        $this->assertArrayHasKey('overall_limit', $rules);
        $this->assertArrayHasKey('month', $rules);
    }

    public function test_update_allocation_validation_rules_are_correct(): void
    {
        $rules = BudgetService::updateAllocationValidationRules();

        $this->assertArrayHasKey('percentages', $rules);
        $this->assertArrayHasKey('month', $rules);
    }
}
