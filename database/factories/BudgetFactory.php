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

    public function definition(): array
    {
        $year = (int) now()->year;
        $month = (int) now()->month;

        return [
            'user_id' => User::factory(),
            'expense_category_id' => ExpenseCategory::factory(),
            'limit' => $this->faker->randomFloat(2, 100, 1000),
            'month' => $month,
            'year' => $year,
        ];
    }

    public function forUserAndCategory(User $user, ExpenseCategory $category): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
        ]);
    }
}

