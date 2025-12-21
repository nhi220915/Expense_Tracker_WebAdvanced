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
        // Use unique name to avoid Duplicate Entry in tests
        $name = $this->faker->unique()->word . ' ' . $this->faker->unique()->randomNumber(5);

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'color' => $this->faker->hexColor(),
        ];
    }
}
