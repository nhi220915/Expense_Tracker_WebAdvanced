<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $now = now();
        $year = $now->year;
        $month = $now->month;

        // Realistic budget limits based on category
        $budgetRanges = [
            'Food' => [300, 800],
            'Transport' => [150, 500],
            'Entertainment' => [100, 400],
            'Utilities' => [100, 600],
            'Shopping' => [200, 1000],
            'Healthcare' => [100, 500],
            'Education' => [200, 2000],
            'Travel' => [500, 3000],
            'Bills' => [500, 2000],
            'Other' => [100, 500],
        ];

        $defaultCategoryName = $this->faker->randomElement(array_keys($budgetRanges));
        $range = $budgetRanges[$defaultCategoryName];
        $limit = $this->faker->randomFloat(2, $range[0], $range[1]);

        return [
            'user_id' => User::factory(),
            'expense_category_id' => ExpenseCategory::factory(),
            'limit' => $limit,
            'month' => $month,
            'year' => $year,
        ];
    }

    /**
     * Create budget for a specific user and category
     */
    public function forUserAndCategory(User $user, ExpenseCategory $category): static
    {
        // Realistic budget limits based on category
        $budgetRanges = [
            'Food' => [300, 800],
            'Transport' => [150, 500],
            'Entertainment' => [100, 400],
            'Utilities' => [100, 600],
            'Shopping' => [200, 1000],
            'Healthcare' => [100, 500],
            'Education' => [200, 2000],
            'Travel' => [500, 3000],
            'Bills' => [500, 2000],
            'Other' => [100, 500],
        ];

        $range = $budgetRanges[$category->name] ?? [100, 500];
        $limit = $this->faker->randomFloat(2, $range[0], $range[1]);

        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'limit' => $limit,
        ]);
    }

    /**
     * Create budget for a specific month
     */
    public function forMonth(int $year, int $month): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => $year,
            'month' => $month,
        ]);
    }
}

