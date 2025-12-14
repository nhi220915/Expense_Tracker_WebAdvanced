<?php

namespace Database\Factories;

use App\Models\Income;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Income>
 */
class IncomeFactory extends Factory
{
    protected $model = Income::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['Salary', 'Freelance', 'Bonus', 'Investment', 'Other Income'];
        $category = $this->faker->randomElement($categories);

        // Realistic notes by category
        $notesByCategory = [
            'Salary' => ['Monthly salary', 'December salary', 'November salary', 'Paycheck', 'Monthly income'],
            'Freelance' => ['Freelance project', 'Web development', 'Design work', 'Consulting', 'Part-time work'],
            'Bonus' => ['Year-end bonus', 'Performance bonus', 'Holiday bonus', 'Quarterly bonus'],
            'Investment' => ['Stock dividends', 'Investment return', 'Dividend payment', 'ROI'],
            'Other Income' => ['Gift money', 'Refund', 'Side hustle', 'Cashback', 'Reward'],
        ];

        // Realistic amounts by category
        $amountRanges = [
            'Salary' => [2000, 10000],
            'Freelance' => [200, 5000],
            'Bonus' => [500, 5000],
            'Investment' => [50, 2000],
            'Other Income' => [20, 1000],
        ];

        $range = $amountRanges[$category];
        $amount = $this->faker->randomFloat(2, $range[0], $range[1]);

        // For salary, make it more consistent (round to nearest 100)
        if ($category === 'Salary') {
            $amount = round($amount / 100) * 100;
        }

        return [
            'user_id' => User::factory(),
            'amount' => $amount,
            'category' => $category,
            'date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'note' => $this->faker->randomElement($notesByCategory[$category]),
        ];
    }

    /**
     * Create income for a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create income for a specific month
     */
    public function forMonth(int $year, int $month): static
    {
        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return $this->state(fn (array $attributes) => [
            'date' => $this->faker->dateTimeBetween($startDate, $endDate),
        ]);
    }

    /**
     * Create salary income (typically monthly)
     */
    public function salary(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'Salary',
            'amount' => $this->faker->randomFloat(2, 2500, 8000),
            'note' => $this->faker->randomElement(['Monthly salary', 'Paycheck', 'Salary payment']),
        ]);
    }
}
