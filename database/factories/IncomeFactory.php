<?php

namespace Database\Factories;

use App\Models\Income;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Income>
 */
class IncomeFactory extends Factory
{
    protected $model = Income::class;

    public function definition(): array
    {
        $categories = ['Salary', 'Freelance', 'Bonus', 'Investment', 'Other Income'];
        $category = $this->faker->randomElement($categories);

        $ranges = [
            'Salary' => [2500, 8000],
            'Freelance' => [200, 4000],
            'Bonus' => [300, 5000],
            'Investment' => [50, 2000],
            'Other Income' => [20, 800],
        ];

        $notes = [
            'Salary' => ['Monthly salary', 'Paycheck'],
            'Freelance' => ['Client project', 'Side gig'],
            'Bonus' => ['Performance bonus', 'Holiday bonus'],
            'Investment' => ['Dividend', 'ROI'],
            'Other Income' => ['Refund', 'Gift money'],
        ];

        $range = $ranges[$category] ?? [20, 1000];
        $amount = $this->faker->randomFloat(2, $range[0], $range[1]);
        if ($category === 'Salary') {
            $amount = round($amount / 100) * 100;
        }

        return [
            'user_id' => User::factory(),
            'amount' => $amount,
            'category' => $category,
            'date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'note' => $this->faker->randomElement($notes[$category] ?? ['Income']),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }

    public function forMonth(int $year, int $month): static
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        return $this->state(fn () => [
            'date' => $this->faker->dateTimeBetween($start, $end),
        ]);
    }

    public function salary(): static
    {
        return $this->state(fn () => [
            'category' => 'Salary',
            'amount' => round($this->faker->randomFloat(2, 2500, 8000) / 100) * 100,
            'note' => $this->faker->randomElement(['Monthly salary', 'Paycheck']),
        ]);
    }
}

