<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Realistic expense notes by category
        $notesByCategory = [
            'Food' => ['Highland Coffee', 'McDonald\'s', 'Pizza Hut', 'Grocery Store', 'Restaurant', 'Starbucks', 'Lunch with colleagues', 'Dinner date'],
            'Transport' => ['Uber ride', 'Gas station', 'Bus ticket', 'Parking fee', 'Taxi', 'Metro card', 'Car maintenance', 'Train ticket'],
            'Entertainment' => ['Movie tickets', 'Concert', 'Netflix subscription', 'Video games', 'Books', 'Theater', 'Sports event', 'Gaming'],
            'Utilities' => ['Electricity bill', 'Water bill', 'Internet', 'Phone bill', 'Gas bill', 'Cable TV'],
            'Shopping' => ['Amazon purchase', 'Clothing store', 'Electronics', 'Home decor', 'Online shopping'],
            'Healthcare' => ['Pharmacy', 'Doctor visit', 'Dental checkup', 'Medicine', 'Health insurance'],
            'Education' => ['Course fee', 'Books', 'Online course', 'Workshop', 'Tuition'],
            'Travel' => ['Hotel booking', 'Flight ticket', 'Travel insurance', 'Souvenirs'],
            'Bills' => ['Rent', 'Insurance', 'Loan payment', 'Credit card'],
            'Other' => ['Miscellaneous', 'Gift', 'Donation', 'Repair', 'Service fee'],
        ];

        // Realistic amounts based on category
        $amountRanges = [
            'Food' => [5, 150],
            'Transport' => [2, 100],
            'Entertainment' => [10, 200],
            'Utilities' => [30, 500],
            'Shopping' => [20, 1000],
            'Healthcare' => [15, 500],
            'Education' => [50, 2000],
            'Travel' => [100, 5000],
            'Bills' => [50, 2000],
            'Other' => [5, 500],
        ];

        // Get a random category name for default state
        $defaultCategoryName = $this->faker->randomElement(array_keys($notesByCategory));
        $range = $amountRanges[$defaultCategoryName] ?? [5, 200];
        $amount = $this->faker->randomFloat(2, $range[0], $range[1]);
        $note = $this->faker->randomElement($notesByCategory[$defaultCategoryName] ?? ['Miscellaneous expense']);

        return [
            'user_id' => User::factory(),
            'expense_category_id' => ExpenseCategory::factory(),
            'amount' => $amount,
            'date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'note' => $note,
        ];
    }

    /**
     * Create expense for a specific user and category
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create expense for a specific category
     */
    public function forCategory(ExpenseCategory $category): static
    {
        $categoryName = $category->name;
        
        // Realistic expense notes by category
        $notesByCategory = [
            'Food' => ['Highland Coffee', 'McDonald\'s', 'Pizza Hut', 'Grocery Store', 'Restaurant', 'Starbucks', 'Lunch with colleagues', 'Dinner date'],
            'Transport' => ['Uber ride', 'Gas station', 'Bus ticket', 'Parking fee', 'Taxi', 'Metro card', 'Car maintenance', 'Train ticket'],
            'Entertainment' => ['Movie tickets', 'Concert', 'Netflix subscription', 'Video games', 'Books', 'Theater', 'Sports event', 'Gaming'],
            'Utilities' => ['Electricity bill', 'Water bill', 'Internet', 'Phone bill', 'Gas bill', 'Cable TV'],
            'Shopping' => ['Amazon purchase', 'Clothing store', 'Electronics', 'Home decor', 'Online shopping'],
            'Healthcare' => ['Pharmacy', 'Doctor visit', 'Dental checkup', 'Medicine', 'Health insurance'],
            'Education' => ['Course fee', 'Books', 'Online course', 'Workshop', 'Tuition'],
            'Travel' => ['Hotel booking', 'Flight ticket', 'Travel insurance', 'Souvenirs'],
            'Bills' => ['Rent', 'Insurance', 'Loan payment', 'Credit card'],
            'Other' => ['Miscellaneous', 'Gift', 'Donation', 'Repair', 'Service fee'],
        ];

        $notes = $notesByCategory[$categoryName] ?? ['Miscellaneous expense'];
        $note = $this->faker->randomElement($notes);

        // Realistic amounts based on category
        $amountRanges = [
            'Food' => [5, 150],
            'Transport' => [2, 100],
            'Entertainment' => [10, 200],
            'Utilities' => [30, 500],
            'Shopping' => [20, 1000],
            'Healthcare' => [15, 500],
            'Education' => [50, 2000],
            'Travel' => [100, 5000],
            'Bills' => [50, 2000],
            'Other' => [5, 500],
        ];

        $range = $amountRanges[$categoryName] ?? [5, 200];
        $amount = $this->faker->randomFloat(2, $range[0], $range[1]);

        return $this->state(fn (array $attributes) => [
            'user_id' => $category->user_id,
            'expense_category_id' => $category->id,
            'amount' => $amount,
            'note' => $note,
        ]);
    }

    /**
     * Create expense for a specific month
     */
    public function forMonth(int $year, int $month): static
    {
        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return $this->state(fn (array $attributes) => [
            'date' => $this->faker->dateTimeBetween($startDate, $endDate),
        ]);
    }
}

