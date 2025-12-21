<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendBudgetWarningEmailJob;
use App\Jobs\SendMonthlyExpenseSummaryJob;
use App\Jobs\SendMissingExpenseReminderJob;
use App\Models\Expense;
use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Mail\BudgetWarningMail;
use App\Mail\MonthlyExpenseSummaryMail;
use App\Mail\MissingExpenseReminderMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailJobsTest extends TestCase
{
    use RefreshDatabase;

    public function test_budget_warning_job_execution(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'limit' => 1000
        ]);

        $job = new SendBudgetWarningEmailJob($user, $budget, 85.5);
        $job->handle();

        Mail::assertSent(BudgetWarningMail::class);
    }

    public function test_monthly_expense_summary_job_execution(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id, 'name' => 'Food']);
        Expense::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'amount' => 500,
            'date' => now()->format('Y-m-d')
        ]);

        $job = new SendMonthlyExpenseSummaryJob($user);
        $job->handle();

        Mail::assertSent(MonthlyExpenseSummaryMail::class);
    }

    public function test_missing_expense_reminder_job_execution(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $job = new SendMissingExpenseReminderJob($user, 3);
        $job->handle();

        Mail::assertSent(MissingExpenseReminderMail::class);
    }

    public function test_jobs_configuration(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
        ]);

        $job = new SendBudgetWarningEmailJob($user, $budget, 85.5);
        $this->assertEquals('emails', $job->queue);
        $this->assertTrue($job->deleteWhenMissingModels);
    }
}
