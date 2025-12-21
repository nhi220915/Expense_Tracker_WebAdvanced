<?php

namespace Database\Seeders;

use App\Models\Income;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class IncomeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $now = Carbon::now();

        foreach ($users as $user) {
            for ($i = 0; $i < 6; $i++) {
                $target = $now->copy()->subMonths($i);
                $year = $target->year;
                $month = $target->month;

                // Salary once per month near start
                Income::factory()
                    ->forUser($user)
                    ->salary()
                    ->forMonth($year, $month)
                    ->create([
                        'date' => Carbon::create($year, $month, rand(1, 5)),
                    ]);

                // 30% chance of extra income
                if (rand(1, 100) <= 30) {
                    Income::factory()
                        ->forUser($user)
                        ->forMonth($year, $month)
                        ->count(1)
                        ->create();
                }
            }
        }
    }
}

