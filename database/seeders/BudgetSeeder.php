<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BudgetSeeder extends Seeder
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

            // Seed budgets for current + previous 2 months
            for ($i = 0; $i < 3; $i++) {
                $target = $now->copy()->subMonths($i);
                $year = $target->year;
                $month = $target->month;

                $total = rand(2000, 5000);
                $perCat = round($total / max(1, $categories->count()), 2);

                foreach ($categories as $idx => $category) {
                    $limit = $idx === $categories->count() - 1
                        ? round($total - ($perCat * ($categories->count() - 1)), 2)
                        : $perCat;

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
        }
    }
}

