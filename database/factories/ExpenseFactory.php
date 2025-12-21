<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        $notesByCategory = [
            'Food' => ['Highland Coffee', 'Grocery Store', 'Restaurant', 'Lunch', 'Dinner'],
            'Transport' => ['Uber ride', 'Gas station', 'Parking fee', 'Bus ticket'],
            'Entertainment' => ['Movie tickets', 'Netflix subscription', 'Concert'],
            'Utilities' => ['Electricity bill', 'Water bill', 'Internet'],
            'Other' => ['Gift', 'Donation', 'Service fee', 'Miscellaneous'],
        ];

        $amountRanges = [
            'Food' => [5, 150],
            'Transport' => [2, 100],
            'Entertainment' => [10, 200],
            'Utilities' => [30, 500],
            'Other' => [5, 500],
        ];

        $categoryName = $this->faker->randomElement(array_keys($notesByCategory));
        $range = $amountRanges[$categoryName] ?? [5, 200];

        return [
            'user_id' => User::factory(),
            'expense_category_id' => ExpenseCategory::factory(),
            'amount' => $this->faker->randomFloat(2, $range[0], $range[1]),
            'date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'note' => $this->faker->randomElement($notesByCategory[$categoryName] ?? ['Miscellaneous']),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }

    public function forCategory(ExpenseCategory $category): static
    {
        // Ensure user_id aligns with category owner
        return $this->state(fn () => [
            'user_id' => $category->user_id,
            'expense_category_id' => $category->id,
        ]);
    }

    public function forMonth(int $year, int $month): static
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        return $this->state(fn () => [
            'date' => $this->faker->dateTimeBetween($start, $end),
        ]);
    }
}

