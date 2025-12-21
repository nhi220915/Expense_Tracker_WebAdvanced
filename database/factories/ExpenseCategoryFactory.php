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

    public function definition(): array
    {
        $palette = [
            'Food' => '#00d1c1',
            'Transport' => '#27ae60',
            'Entertainment' => '#f39c12',
            'Utilities' => '#e74c3c',
            'Other' => '#00a896',
        ];

        $name = $this->faker->randomElement(array_keys($palette));

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'color' => $palette[$name] ?? '#cccccc',
        ];
    }
}

