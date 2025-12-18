<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run()
    {
        $defaultCategories = ['Food', 'Transport', 'Entertainment', 'Utilities', 'Other'];

        // Create default categories for all users or for a specific user
        $users = User::all();
        
        foreach ($users as $user) {
            foreach ($defaultCategories as $categoryName) {
                ExpenseCategory::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'name' => $categoryName,
                    ],
                    [
                        'color' => $this->getCategoryColor($categoryName),
                    ]
                );
            }
        }
    }

    private function getCategoryColor($categoryName)
    {
        $colors = [
            'Food' => '#00d1c1',
            'Transport' => '#27ae60',
            'Entertainment' => '#f39c12',
            'Utilities' => '#e74c3c',
            'Other' => '#00a896',
        ];

        return $colors[$categoryName] ?? '#cccccc';
    }
}