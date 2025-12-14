<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please seed users first.');
            return;
        }

        // Comprehensive category list with colors
        $defaultCategories = [
            'Food' => '#00d1c1',
            'Transport' => '#27ae60',
            'Entertainment' => '#f39c12',
            'Utilities' => '#e74c3c',
            'Shopping' => '#9b59b6',
            'Healthcare' => '#3498db',
            'Education' => '#1abc9c',
            'Travel' => '#e67e22',
            'Bills' => '#c0392b',
            'Other' => '#00a896',
        ];

        foreach ($users as $user) {
            foreach ($defaultCategories as $categoryName => $color) {
                ExpenseCategory::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'name' => $categoryName,
                    ],
                    [
                        'color' => $color,
                    ]
                );
            }

            $this->command->info("✓ Seeded categories for user: {$user->name}");
        }
    }
}