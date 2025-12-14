<?php

namespace Database\Seeders;

use App\Models\Income;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class IncomeSeeder extends Seeder
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
            // Generate incomes for the last 6 months
            $monthsToSeed = 6;
            $now = Carbon::now();

            for ($monthOffset = 0; $monthOffset < $monthsToSeed; $monthOffset++) {
                $targetDate = $now->copy()->subMonths($monthOffset);
                $year = $targetDate->year;
                $month = $targetDate->month;

                // Each user gets 1-2 salary payments per month (usually 1)
                Income::factory()
                    ->count(1)
                    ->forUser($user)
                    ->salary()
                    ->forMonth($year, $month)
                    ->create([
                        'date' => Carbon::create($year, $month, rand(1, 5)), // Salary usually comes at the start of month
                    ]);

                // Occasionally add bonus or freelance income (30% chance)
                if (rand(1, 100) <= 30) {
                    $incomeTypes = ['Freelance', 'Bonus', 'Investment'];
                    $type = $incomeTypes[array_rand($incomeTypes)];

                    Income::factory()
                        ->count(1)
                        ->forUser($user)
                        ->forMonth($year, $month)
                        ->create([
                            'category' => $type,
                        ]);
                }
            }

            $this->command->info("✓ Seeded incomes for user: {$user->name}");
        }
    }
}
