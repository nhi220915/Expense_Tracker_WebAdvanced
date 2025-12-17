<?php

namespace Database\Factories;

use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpenseCategory>
 */
class ExpenseCategoryFactory extends Factory
{
    protected $model = ExpenseCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Food' => '#00d1c1',
            'Transport' => '#27ae60',
            'Entertainment' => '#f39c12',
            'Utilities' => '#e74c3c',
            'Other' => '#00a896',
            'Shopping' => '#9b59b6',
            'Healthcare' => '#3498db',
            'Education' => '#1abc9c',
            'Travel' => '#e67e22',
            'Bills' => '#c0392b',
        ];

        $category = $this->faker->randomElement(array_keys($categories));

        return [
            'user_id' => User::factory(),
            'name' => $category,
            'color' => $categories[$category] ?? '#cccccc',
        ];
    }
}

