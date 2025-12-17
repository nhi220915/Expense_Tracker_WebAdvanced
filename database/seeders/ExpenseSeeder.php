<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ExpenseSeeder extends Seeder
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

        foreach ($users as $user) {
            $categories = ExpenseCategory::where('user_id', $user->id)->get();

            if ($categories->isEmpty()) {
                $this->command->warn("No categories found for user {$user->name}. Skipping expenses.");
                continue;
            }

            // Generate expenses for the last 6 months
            $monthsToSeed = 6;
            $now = Carbon::now();

            for ($monthOffset = 0; $monthOffset < $monthsToSeed; $monthOffset++) {
                $targetDate = $now->copy()->subMonths($monthOffset);
                $year = $targetDate->year;
                $month = $targetDate->month;

                // Generate 15-30 expenses per month per user
                $expensesPerMonth = rand(15, 30);

                foreach ($categories as $category) {
                    // Generate 2-8 expenses per category per month
                    $expensesPerCategory = rand(2, 8);

                    Expense::factory()
                        ->count($expensesPerCategory)
                        ->forUser($user)
                        ->forCategory($category)
                        ->forMonth($year, $month)
                        ->create();
                }
            }

            $this->command->info("✓ Seeded expenses for user: {$user->name}");
        }
    }
}

