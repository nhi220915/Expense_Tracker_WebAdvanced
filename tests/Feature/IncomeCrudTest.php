<?php

namespace Tests\Feature;

use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomeCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_incomes_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('incomes.index'))
            ->assertOk();
    }

    public function test_user_can_create_income(): void
    {
        $user = User::factory()->create();

        $payload = [
            'amount' => 300.00,
            'category' => 'Salary',
            'date' => '2025-12-14',
            'note' => 'December salary',
        ];

        $this->actingAs($user)
            ->post(route('incomes.store'), $payload)
            ->assertRedirect(route('incomes.index', ['month' => '2025-12'], absolute: false));

        $this->assertDatabaseHas('incomes', [
            'user_id' => $user->id,
            'category' => 'Salary',
            'amount' => 300.00,
            'note' => 'December salary',
        ]);
    }

    public function test_user_can_update_own_income(): void
    {
        $user = User::factory()->create();
        $income = Income::factory()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'category' => 'Salary',
            'date' => '2025-12-01',
        ]);

        $this->actingAs($user)
            ->put(route('incomes.update', $income), [
                'amount' => 200.50,
                'category' => 'Bonus',
                'date' => '2025-12-02',
                'note' => 'Updated',
            ])
            ->assertRedirect(route('incomes.index', ['month' => '2025-12'], absolute: false));

        $this->assertDatabaseHas('incomes', [
            'id' => $income->id,
            'user_id' => $user->id,
            'amount' => 200.50,
            'category' => 'Bonus',
            'note' => 'Updated',
        ]);
    }

    public function test_user_cannot_update_other_users_income(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $income = Income::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($other)
            ->put(route('incomes.update', $income), [
                'amount' => 1,
                'category' => 'Salary',
                'date' => '2025-12-02',
            ])
            ->assertForbidden();
    }

    public function test_user_can_delete_own_income(): void
    {
        $user = User::factory()->create();
        $income = Income::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->delete(route('incomes.destroy', $income))
            ->assertRedirect();

        $this->assertDatabaseMissing('incomes', [
            'id' => $income->id,
        ]);
    }
}

