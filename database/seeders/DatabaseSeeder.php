<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a few users for testing
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->count(3)->create();

        // Seed core data
        $this->call([
            ExpenseCategorySeeder::class,
            IncomeSeeder::class,
            ExpenseSeeder::class,
            BudgetSeeder::class,
        ]);
    }
}
