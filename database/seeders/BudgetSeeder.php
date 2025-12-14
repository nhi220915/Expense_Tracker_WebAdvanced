<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BudgetSeeder extends Seeder
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

        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->month;

        // Seed budgets for current month and previous 2 months
        $monthsToSeed = 3;

        foreach ($users as $user) {
            $categories = ExpenseCategory::where('user_id', $user->id)->get();

            if ($categories->isEmpty()) {
                $this->command->warn("No categories found for user {$user->name}. Skipping budgets.");
                continue;
            }

            // Calculate total monthly budget (realistic range: $2000 - $5000)
            $totalMonthlyBudget = rand(2000, 5000);

            // Budget allocation percentages (should sum to 100%)
            $allocations = [
                'Food' => 25,
                'Transport' => 15,
                'Entertainment' => 10,
                'Utilities' => 12,
                'Shopping' => 15,
                'Healthcare' => 8,
                'Education' => 5,
                'Travel' => 3,
                'Bills' => 5,
                'Other' => 2,
            ];

            for ($monthOffset = 0; $monthOffset < $monthsToSeed; $monthOffset++) {
                $targetDate = $now->copy()->subMonths($monthOffset);
                $year = $targetDate->year;
                $month = $targetDate->month;

                $totalAllocated = 0;
                $categoryCount = $categories->count();
                $remainingPercent = 100;

                foreach ($categories as $index => $category) {
                    // Get allocation percentage for this category
                    $percentage = $allocations[$category->name] ?? (100 / $categoryCount);
                    
                    // For the last category, use remaining percentage to ensure total is 100%
                    if ($index === $categoryCount - 1) {
                        $percentage = $remainingPercent;
                    } else {
                        $remainingPercent -= $percentage;
                    }

                    $limit = round(($percentage / 100) * $totalMonthlyBudget, 2);

                    Budget::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'expense_category_id' => $category->id,
                            'year' => $year,
                            'month' => $month,
                        ],
                        [
                            'limit' => $limit,
                        ]
                    );
                }
            }

            $this->command->info("✓ Seeded budgets for user: {$user->name}");
        }
    }
}
