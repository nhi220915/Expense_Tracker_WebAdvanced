<?php

namespace Tests\Feature\Api;

use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_incomes(): void
    {
        $user = User::factory()->create();
        Income::factory()->count(3)->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/incomes');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_income(): void
    {
        $user = User::factory()->create();

        $payload = [
            'category' => 'Salary',
            'amount' => 5000.00,
            'date' => '2025-12-01',
            'note' => 'December Salary',
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/incomes', $payload);

        $response->assertCreated();

        $this->assertDatabaseHas('incomes', [
            'user_id' => $user->id,
            'amount' => 5000.00,
            'category' => 'Salary',
        ]);
    }

    public function test_user_can_update_income(): void
    {
        $user = User::factory()->create();
        $income = Income::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->putJson("/api/incomes/{$income->id}", [
            'category' => 'Updated Source',
            'amount' => 6000.00,
            'date' => '2025-12-01',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('incomes', [
            'id' => $income->id,
            'amount' => 6000.00,
        ]);
    }

    public function test_user_can_delete_income(): void
    {
        $user = User::factory()->create();
        $income = Income::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/incomes/{$income->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('incomes', ['id' => $income->id]);
    }
}
