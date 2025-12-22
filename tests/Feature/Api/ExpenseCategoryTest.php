<?php

namespace Tests\Feature\Api;

use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_categories(): void
    {
        $user = User::factory()->create();
        ExpenseCategory::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/expense-categories');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_category(): void
    {
        $user = User::factory()->create();

        $payload = [
            'name' => 'New Category',
            'color' => '#ff0000',
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/expense-categories', $payload);

        $response->assertCreated();

        $this->assertDatabaseHas('expense_categories', [
            'user_id' => $user->id,
            'name' => 'New Category',
        ]);
    }

    public function test_user_can_update_category(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        $payload = [
            'name' => 'Updated Name',
            'color' => '#00ff00',
        ];

        $response = $this->actingAs($user, 'sanctum')->putJson("/api/expense-categories/{$category->id}", $payload);

        $response->assertOk();

        $this->assertDatabaseHas('expense_categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_user_cannot_update_others_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')->putJson("/api/expense-categories/{$category->id}", [
            'name' => 'Hacked',
            'color' => '#000000',
        ]);

        $response->assertForbidden(); // Or 403
    }

    public function test_user_can_delete_category(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/expense-categories/{$category->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('expense_categories', ['id' => $category->id]);
    }
}
