<?php

namespace Tests\Unit\Services;

use App\Models\Income;
use App\Models\User;
use App\Services\IncomeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected IncomeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new IncomeService();
    }

    public function test_list_for_user_by_month_returns_incomes_for_given_month(): void
    {
        $user = User::factory()->create();

        // Create incomes in different months
        Income::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-12-15',
            'category' => 'Salary',
        ]);
        Income::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-11-10',
            'category' => 'Bonus',
        ]);

        $incomes = $this->service->listForUserByMonth($user, '2025-12');

        $this->assertCount(1, $incomes);
        $this->assertEquals('2025-12-15', $incomes->first()->date->format('Y-m-d'));
    }

    public function test_list_for_user_by_month_orders_by_date_descending(): void
    {
        $user = User::factory()->create();

        Income::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-12-10',
        ]);
        Income::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-12-20',
        ]);

        $incomes = $this->service->listForUserByMonth($user, '2025-12');

        $this->assertEquals('2025-12-20', $incomes->first()->date->format('Y-m-d'));
        $this->assertEquals('2025-12-10', $incomes->last()->date->format('Y-m-d'));
    }

    public function test_create_income_successfully(): void
    {
        $user = User::factory()->create();

        $data = [
            'amount' => 5000.00,
            'category' => 'Salary',
            'date' => '2025-12-01',
            'note' => 'December salary',
        ];

        $income = $this->service->create($user, $data);

        $this->assertInstanceOf(Income::class, $income);
        $this->assertEquals(5000.00, $income->amount);
        $this->assertEquals('Salary', $income->category);
        $this->assertEquals($user->id, $income->user_id);
        $this->assertDatabaseHas('incomes', [
            'user_id' => $user->id,
            'amount' => 5000.00,
            'category' => 'Salary',
            'note' => 'December salary',
        ]);
    }

    public function test_update_income_successfully_when_user_owns_income(): void
    {
        $user = User::factory()->create();
        $income = Income::factory()->create([
            'user_id' => $user->id,
            'amount' => 3000.00,
            'category' => 'Salary',
        ]);

        $data = [
            'amount' => 3500.00,
            'category' => 'Bonus',
            'date' => '2025-12-20',
            'note' => 'Updated income',
        ];

        $updatedIncome = $this->service->update($user, $income, $data);

        $this->assertEquals(3500.00, $updatedIncome->amount);
        $this->assertEquals('Bonus', $updatedIncome->category);
        $this->assertEquals('Updated income', $updatedIncome->note);
        $this->assertDatabaseHas('incomes', [
            'id' => $income->id,
            'amount' => 3500.00,
            'category' => 'Bonus',
        ]);
    }

    public function test_update_income_throws_403_when_user_does_not_own_income(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $income = Income::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Unauthorized action');

        $this->service->update($otherUser, $income, [
            'amount' => 1000,
            'category' => 'Salary',
            'date' => '2025-12-20',
        ]);
    }

    public function test_delete_income_successfully_when_user_owns_income(): void
    {
        $user = User::factory()->create();
        $income = Income::factory()->create([
            'user_id' => $user->id,
        ]);

        $result = $this->service->delete($user, $income);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('incomes', [
            'id' => $income->id,
        ]);
    }

    public function test_delete_income_throws_403_when_user_does_not_own_income(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $income = Income::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Unauthorized action');

        $this->service->delete($otherUser, $income);
    }

    public function test_validation_rules_are_correct(): void
    {
        $rules = IncomeService::validationRules();

        $this->assertArrayHasKey('amount', $rules);
        $this->assertArrayHasKey('category', $rules);
        $this->assertArrayHasKey('date', $rules);
        $this->assertArrayHasKey('note', $rules);

        $this->assertStringContainsString('required', $rules['amount']);
        $this->assertStringContainsString('numeric', $rules['amount']);
        $this->assertStringContainsString('required', $rules['category']);
        $this->assertStringContainsString('required', $rules['date']);
    }
}
