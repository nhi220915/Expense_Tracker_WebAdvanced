<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\TestQueueEmails;
use App\Jobs\SendBudgetWarningEmailJob;
use App\Jobs\SendMonthlyExpenseSummaryJob;
use App\Jobs\SendMissingExpenseReminderJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TestQueueEmailsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_command_returns_error_when_no_users_exist(): void
    {
        $this->artisan('test:queue-emails')
            ->expectsOutput('❌ No users found in database!')
            ->assertExitCode(1);
    }

    public function test_command_dispatches_all_jobs_by_default(): void
    {
        $user = User::factory()->create();

        $this->artisan('test:queue-emails')
            ->assertExitCode(0);

        Queue::assertPushed(SendBudgetWarningEmailJob::class);
        Queue::assertPushed(SendMonthlyExpenseSummaryJob::class);
        Queue::assertPushed(SendMissingExpenseReminderJob::class);
    }

    public function test_command_dispatches_only_budget_warning_when_specified(): void
    {
        $user = User::factory()->create();

        $this->artisan('test:queue-emails', ['type' => 'budget'])
            ->assertExitCode(0);

        Queue::assertPushed(SendBudgetWarningEmailJob::class);
        Queue::assertNotPushed(SendMonthlyExpenseSummaryJob::class);
        Queue::assertNotPushed(SendMissingExpenseReminderJob::class);
    }

    public function test_command_dispatches_only_monthly_summary_when_specified(): void
    {
        $user = User::factory()->create();

        $this->artisan('test:queue-emails', ['type' => 'monthly'])
            ->assertExitCode(0);

        Queue::assertPushed(SendMonthlyExpenseSummaryJob::class);
        Queue::assertNotPushed(SendBudgetWarningEmailJob::class);
        Queue::assertNotPushed(SendMissingExpenseReminderJob::class);
    }

    public function test_command_dispatches_only_reminder_when_specified(): void
    {
        $user = User::factory()->create();

        $this->artisan('test:queue-emails', ['type' => 'reminder'])
            ->assertExitCode(0);

        Queue::assertPushed(SendMissingExpenseReminderJob::class);
        Queue::assertNotPushed(SendBudgetWarningEmailJob::class);
        Queue::assertNotPushed(SendMonthlyExpenseSummaryJob::class);
    }

    public function test_command_outputs_success_messages(): void
    {
        $user = User::factory()->create();

        $this->artisan('test:queue-emails')
            ->expectsOutput('🧪 Testing Queue Email System')
            ->expectsOutput('✅ Jobs dispatched successfully!')
            ->assertExitCode(0);
    }

    public function test_command_displays_user_information(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $this->artisan('test:queue-emails')
            ->expectsOutputToContain('👤 User: Test User (test@example.com)')
            ->assertExitCode(0);
    }
}
