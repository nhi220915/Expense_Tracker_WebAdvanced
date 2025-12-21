<?php

namespace Tests\Unit\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Services\ExpenseCategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseCategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ExpenseCategoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ExpenseCategoryService();
    }

    public function test_list_for_user_returns_categories_ordered_by_name(): void
    {
        $user = User::factory()->create();

        ExpenseCategory::factory()->create(['user_id' => $user->id, 'name' => 'Transport']);
        ExpenseCategory::factory()->create(['user_id' => $user->id, 'name' => 'Food']);
        ExpenseCategory::factory()->create(['user_id' => $user->id, 'name' => 'Entertainment']);

        $categories = $this->service->listForUser($user);

        $this->assertCount(3, $categories);
        $this->assertEquals('Entertainment', $categories->first()->name);
        $this->assertEquals('Transport', $categories->last()->name);
    }

    public function test_list_for_user_returns_only_user_categories(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        ExpenseCategory::factory()->create(['user_id' => $user1->id]);
        ExpenseCategory::factory()->create(['user_id' => $user2->id]);

        $categories = $this->service->listForUser($user1);

        $this->assertCount(1, $categories);
        $this->assertEquals($user1->id, $categories->first()->user_id);
    }

    public function test_create_category_successfully(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Shopping',
            'color' => '#ff5733',
        ];

        $category = $this->service->create($user, $data);

        $this->assertInstanceOf(ExpenseCategory::class, $category);
        $this->assertEquals('Shopping', $category->name);
        $this->assertEquals('#ff5733', $category->color);
        $this->assertEquals($user->id, $category->user_id);
        $this->assertDatabaseHas('expense_categories', [
            'user_id' => $user->id,
            'name' => 'Shopping',
            'color' => '#ff5733',
        ]);
    }

    public function test_create_category_uses_default_color_when_not_provided(): void
    {
        $user = User::factory()->create();

        $data = ['name' => 'Shopping'];

        $category = $this->service->create($user, $data);

        $this->assertEquals('#00a896', $category->color);
    }

    public function test_update_category_successfully_when_user_owns_category(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create([
            'user_id' => $user->id,
            'name' => 'Food',
            'color' => '#000000',
        ]);

        $data = [
            'name' => 'Groceries',
            'color' => '#ffffff',
        ];

        $updatedCategory = $this->service->update($user, $category, $data);

        $this->assertEquals('Groceries', $updatedCategory->name);
        $this->assertEquals('#ffffff', $updatedCategory->color);
        $this->assertDatabaseHas('expense_categories', [
            'id' => $category->id,
            'name' => 'Groceries',
            'color' => '#ffffff',
        ]);
    }

    public function test_update_category_throws_403_when_user_does_not_own_category(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $owner->id]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Unauthorized action');

        $this->service->update($otherUser, $category, ['name' => 'New Name']);
    }

    public function test_delete_category_successfully_when_user_owns_category_and_no_expenses(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);

        $result = $this->service->delete($user, $category);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('expense_categories', [
            'id' => $category->id,
        ]);
    }

    public function test_delete_category_throws_403_when_user_does_not_own_category(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $owner->id]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Unauthorized action');

        $this->service->delete($otherUser, $category);
    }

    public function test_delete_category_throws_exception_when_category_has_expenses(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);
        Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete category with existing expenses!');

        $this->service->delete($user, $category);
    }

    public function test_create_default_categories_creates_five_categories(): void
    {
        $user = User::factory()->create();

        $this->service->createDefaultCategories($user);

        $categories = ExpenseCategory::where('user_id', $user->id)->get();

        $this->assertCount(5, $categories);
        $categoryNames = $categories->pluck('name')->toArray();
        $this->assertContains('Food', $categoryNames);
        $this->assertContains('Transport', $categoryNames);
        $this->assertContains('Entertainment', $categoryNames);
        $this->assertContains('Utilities', $categoryNames);
        $this->assertContains('Other', $categoryNames);
    }

    public function test_create_default_categories_does_not_duplicate_existing_categories(): void
    {
        $user = User::factory()->create();
        ExpenseCategory::factory()->create(['user_id' => $user->id, 'name' => 'Food']);

        $this->service->createDefaultCategories($user);

        $categories = ExpenseCategory::where('user_id', $user->id)->get();

        // Should still have 5 categories (not duplicating Food)
        $this->assertCount(5, $categories);
        $foodCategories = $categories->where('name', 'Food');
        $this->assertCount(1, $foodCategories);
    }

    public function test_create_validation_rules_are_correct(): void
    {
        $rules = ExpenseCategoryService::createValidationRules(1);

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('color', $rules);
        $this->assertStringContainsString('required', $rules['name']);
        $this->assertStringContainsString('unique', $rules['name']);
    }

    public function test_update_validation_rules_are_correct(): void
    {
        $rules = ExpenseCategoryService::updateValidationRules(1, 1);

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('color', $rules);
        $this->assertStringContainsString('required', $rules['name']);
        $this->assertStringContainsString('unique', $rules['name']);
    }
}
