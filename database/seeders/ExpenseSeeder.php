<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $now = Carbon::now();

        foreach ($users as $user) {
            $categories = ExpenseCategory::where('user_id', $user->id)->get();
            if ($categories->isEmpty()) {
                continue;
            }

            for ($i = 0; $i < 6; $i++) {
                $target = $now->copy()->subMonths($i);
                $year = $target->year;
                $month = $target->month;

                foreach ($categories as $category) {
                    Expense::factory()
                        ->forCategory($category)
                        ->forMonth($year, $month)
                        ->count(rand(2, 6))
                        ->create();
                }
            }
        }
    }
}

