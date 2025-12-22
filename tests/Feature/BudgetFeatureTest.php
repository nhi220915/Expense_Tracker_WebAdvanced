<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ExpenseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_set_budget(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('budgets.store'), [
                'expense_category_id' => $category->id,
                'limit' => 1000,
                'year' => (int) date('Y'),
                'month' => (int) date('m'),
            ])
            ->assertRedirect();
    }

    public function test_user_can_update_overall_limit(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('budgets.update-limit'), [
                'overall_limit' => 5000,
                'month' => date('Y-m'),
            ])
            ->assertRedirect();
    }

    public function test_user_can_update_allocation(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id, 'name' => 'Food']);

        $this->actingAs($user)
            ->put(route('budgets.update-allocation'), [
                'percentages' => ['Food' => 100],
                'month' => date('Y-m'),
            ])
            ->assertRedirect();
    }

    public function test_update_limit_handles_errors(): void
    {
        $user = User::factory()->create();

        // This will trigger the catch block if we pass something that causes an exception in service
        // For example, if the service throws an error or month format is wrong before controller catches it
        $this->actingAs($user)
            ->put(route('budgets.update-limit'), [
                'overall_limit' => 5000,
                'month' => 'invalid-date', // explode will fail or service will fail
            ])
            ->assertSessionHasErrors();
    }

    public function test_update_limit_handles_service_exception(): void
    {
        $user = User::factory()->create();

        // Mock the service to throw an exception
        $this->mock(\App\Services\BudgetService::class, function ($mock) {
            $mock->shouldReceive('updateOverallLimit')
                ->andThrow(new \Exception('Simulated Service Error'));
            // We need to mock validationRules too or ensure they pass
            $mock->shouldReceive('updateLimitValidationRules')
                ->andReturn([
                    'overall_limit' => 'required|numeric',
                    'month' => 'required|string',
                ]);
        });

        $this->actingAs($user)
            ->put(route('budgets.update-limit'), [
                'overall_limit' => 5000,
                'month' => '2025-12',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Simulated Service Error');
    }
}
