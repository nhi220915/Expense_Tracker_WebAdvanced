<?php

namespace Tests\Unit\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Services\ExpenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ExpenseService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ExpenseService();
    }

    public function test_list_for_user_by_month_returns_expenses_for_given_month(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        // Create expenses in different months
        Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'date' => '2025-12-15',
        ]);
        Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'date' => '2025-11-10',
        ]);

        $expenses = $this->service->listForUserByMonth($user, '2025-12');

        $this->assertCount(1, $expenses);
        $this->assertEquals('2025-12-15', $expenses->first()->date->format('Y-m-d'));
    }

    public function test_list_for_user_by_month_orders_by_date_descending(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'date' => '2025-12-10',
        ]);
        Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'date' => '2025-12-20',
        ]);

        $expenses = $this->service->listForUserByMonth($user, '2025-12');

        $this->assertEquals('2025-12-20', $expenses->first()->date->format('Y-m-d'));
        $this->assertEquals('2025-12-10', $expenses->last()->date->format('Y-m-d'));
    }

    public function test_create_expense_successfully(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        $data = [
            'amount' => 100.50,
            'expense_category_id' => $category->id,
            'date' => '2025-12-15',
            'note' => 'Test expense',
        ];

        $expense = $this->service->create($user, $data);

        $this->assertInstanceOf(Expense::class, $expense);
        $this->assertEquals(100.50, $expense->amount);
        $this->assertEquals($user->id, $expense->user_id);
        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'amount' => 100.50,
            'note' => 'Test expense',
        ]);
    }

    public function test_update_expense_successfully_when_user_owns_expense(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);
        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'amount' => 50.00,
        ]);

        $data = [
            'amount' => 150.75,
            'expense_category_id' => $category->id,
            'date' => '2025-12-20',
            'note' => 'Updated expense',
        ];

        $updatedExpense = $this->service->update($user, $expense, $data);

        $this->assertEquals(150.75, $updatedExpense->amount);
        $this->assertEquals('Updated expense', $updatedExpense->note);
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'amount' => 150.75,
            'note' => 'Updated expense',
        ]);
    }

    public function test_update_expense_throws_403_when_user_does_not_own_expense(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $owner->id]);
        $expense = Expense::factory()->create([
            'user_id' => $owner->id,
            'expense_category_id' => $category->id,
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Unauthorized action');

        $this->service->update($otherUser, $expense, [
            'amount' => 100,
            'expense_category_id' => $category->id,
            'date' => '2025-12-20',
        ]);
    }

    public function test_delete_expense_successfully_when_user_owns_expense(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);
        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
        ]);

        $result = $this->service->delete($user, $expense);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('expenses', [
            'id' => $expense->id,
        ]);
    }

    public function test_delete_expense_throws_403_when_user_does_not_own_expense(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $owner->id]);
        $expense = Expense::factory()->create([
            'user_id' => $owner->id,
            'expense_category_id' => $category->id,
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Unauthorized action');

        $this->service->delete($otherUser, $expense);
    }

    public function test_validation_rules_are_correct(): void
    {
        $rules = ExpenseService::validationRules();

        $this->assertArrayHasKey('amount', $rules);
        $this->assertArrayHasKey('expense_category_id', $rules);
        $this->assertArrayHasKey('date', $rules);
        $this->assertArrayHasKey('note', $rules);

        $this->assertStringContainsString('required', $rules['amount']);
        $this->assertStringContainsString('numeric', $rules['amount']);
        $this->assertStringContainsString('required', $rules['expense_category_id']);
        $this->assertStringContainsString('exists', $rules['expense_category_id']);
    }
}
