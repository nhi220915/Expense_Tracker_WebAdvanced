<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SendMonthlyExpenseSummaryJob;
use App\Jobs\SendMissingExpenseReminderJob;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ==================================
// Scheduled Tasks for Email Notifications
// ==================================

Schedule::call(function () {
    // Send monthly expense summary to all users on the 1st of each month
    User::chunk(100, function ($users) {
        foreach ($users as $user) {
            SendMonthlyExpenseSummaryJob::dispatch($user);
        }
    });
})->monthlyOn(1, '09:00')->name('monthly-expense-summary');

Schedule::call(function () {
    // Check for users who haven't logged expenses in 3+ days
    $threeDaysAgo = now()->subDays(3);

    User::whereDoesntHave('expenses', function ($query) use ($threeDaysAgo) {
        $query->where('date', '>=', $threeDaysAgo);
    })->chunk(100, function ($users) use ($threeDaysAgo) {
        foreach ($users as $user) {
            $lastExpense = $user->expenses()->latest('date')->first();
            $daysSince = $lastExpense
                ? now()->diffInDays($lastExpense->date)
                : 30; // Default if no expenses ever

            if ($daysSince >= 3) {
                SendMissingExpenseReminderJob::dispatch($user, $daysSince);
            }
        }
    });
})->dailyAt('18:00')->name('missing-expense-reminder');

// Note: Budget warnings are triggered real-time when expenses are created
// See ExpenseObserver or ExpenseController for implementation

