<?php

namespace Tests\Feature;

use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_expense_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('expense-categories.store'), [
                'name' => 'New Category',
                'color' => '#123456',
            ]);

        $response->assertSessionHasNoErrors();
        // Redirect depends on where the form was submitted, usually back
        $response->assertRedirect();

        $this->assertDatabaseHas('expense_categories', [
            'user_id' => $user->id,
            'name' => 'New Category',
            'color' => '#123456',
        ]);
    }

    public function test_user_can_update_own_category(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->put(route('expense-categories.update', $category), [
                'name' => 'Updated Name',
                'color' => '#abcdef',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('expense_categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
            'color' => '#abcdef',
        ]);
    }

    public function test_user_cannot_update_others_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)
            ->put(route('expense-categories.update', $category), [
                'name' => 'Hacker Update',
                'color' => '#000000',
            ]);

        // Should be forbidden (403)
        $response->assertForbidden();

        $this->assertDatabaseMissing('expense_categories', [
            'id' => $category->id,
            'name' => 'Hacker Update',
        ]);
    }

    public function test_user_can_delete_own_category(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->delete(route('expense-categories.destroy', $category));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseMissing('expense_categories', [
            'id' => $category->id,
        ]);
    }
}
