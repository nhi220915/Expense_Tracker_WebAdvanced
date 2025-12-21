<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Budget;
use App\Jobs\SendBudgetWarningEmailJob;
use App\Jobs\SendMonthlyExpenseSummaryJob;
use App\Jobs\SendMissingExpenseReminderJob;

class TestQueueEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:queue-emails {type?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test queue email jobs (budget|monthly|reminder|all)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type') ?? 'all';

        $user = User::first();

        if (!$user) {
            $this->error('❌ No users found in database!');
            return 1;
        }

        $this->info("🧪 Testing Queue Email System");
        $this->info("👤 User: {$user->name} ({$user->email})");
        $this->newLine();

        if ($type === 'budget' || $type === 'all') {
            $this->testBudgetWarning($user);
        }

        if ($type === 'monthly' || $type === 'all') {
            $this->testMonthlySummary($user);
        }

        if ($type === 'reminder' || $type === 'all') {
            $this->testMissingExpenseReminder($user);
        }

        $this->newLine();
        $this->info('✅ Jobs dispatched successfully!');
        $this->info('📊 Check your queue worker terminal to see jobs processing');
        $this->info('📧 Check storage/logs/laravel.log for email logs');

        return 0;
    }

    private function testBudgetWarning(User $user)
    {
        $this->info('📊 Dispatching Budget Warning Job...');

        $budget = $user->budgets()->first();

        if (!$budget) {
            // Create a fake budget for testing
            $budget = new Budget([
                'user_id' => $user->id,
                'limit' => 1000000,
                'spent' => 850000,
            ]);
        }

        SendBudgetWarningEmailJob::dispatch($user, $budget, 85.0);
        $this->line('   → Budget: ' . number_format($budget->limit ?? 1000000) . ' VNĐ');
        $this->line('   → Spent: ' . number_format($budget->spent ?? 850000) . ' VNĐ (85%)');
    }

    private function testMonthlySummary(User $user)
    {
        $this->info('📈 Dispatching Monthly Summary Job...');

        SendMonthlyExpenseSummaryJob::dispatch($user);
        $this->line('   → Will calculate expenses for current month');
    }

    private function testMissingExpenseReminder(User $user)
    {
        $this->info('💡 Dispatching Missing Expense Reminder Job...');

        SendMissingExpenseReminderJob::dispatch($user, 5);
        $this->line('   → Days since last expense: 5');
    }
}
