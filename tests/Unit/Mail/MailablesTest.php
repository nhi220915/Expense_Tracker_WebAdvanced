<?php

namespace Tests\Unit\Mail;

use App\Mail\BudgetWarningMail;
use App\Mail\MonthlyExpenseSummaryMail;
use App\Mail\MissingExpenseReminderMail;
use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_budget_warning_mail_can_be_created(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id, 'name' => 'Food']);
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'limit' => 1000.00,
        ]);

        $mailable = new BudgetWarningMail($user, $budget, 85.5);

        $this->assertInstanceOf(BudgetWarningMail::class, $mailable);
        $this->assertEquals($user->id, $mailable->user->id);
        $this->assertEquals(85.5, $mailable->percentageUsed);
    }

    public function test_monthly_expense_summary_mail_can_be_created(): void
    {
        $user = User::factory()->create(['name' => 'Jane Smith']);

        $mailable = new MonthlyExpenseSummaryMail($user, 1500.50, [
            'Food' => 500.00,
            'Transport' => 300.00,
        ]);

        $this->assertInstanceOf(MonthlyExpenseSummaryMail::class, $mailable);
        $this->assertEquals($user->id, $mailable->user->id);
        $this->assertEquals(1500.50, $mailable->totalSpent);
    }

    public function test_missing_expense_reminder_mail_can_be_created(): void
    {
        $user = User::factory()->create(['name' => 'Bob Johnson']);

        $mailable = new MissingExpenseReminderMail($user, 7);

        $this->assertInstanceOf(MissingExpenseReminderMail::class, $mailable);
        $this->assertEquals($user->id, $mailable->user->id);
        $this->assertEquals(7, $mailable->daysSinceLastExpense);
    }

    public function test_budget_warning_mail_envelope_has_subject(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id]);
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
        ]);

        $mailable = new BudgetWarningMail($user, $budget, 90.0);
        $envelope = $mailable->envelope();

        $this->assertNotEmpty($envelope->subject);
        $this->assertStringContainsString('Ngân sách', $envelope->subject);
    }

    public function test_mailables_can_be_rendered(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $category = ExpenseCategory::factory()->create(['user_id' => $user->id, 'name' => 'Food']);
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'expense_category_id' => $category->id,
            'limit' => 1000
        ]);

        $budgetWarning = new BudgetWarningMail($user, $budget, 85.5);
        $monthlySummary = new MonthlyExpenseSummaryMail($user, 1000.00, [
            ['category' => 'Food', 'total' => 500, 'count' => 5]
        ]);
        $reminder = new MissingExpenseReminderMail($user, 5);

        // Assert they can be rendered without errors
        $this->assertNotEmpty($budgetWarning->render());
        $this->assertNotEmpty($monthlySummary->render());
        $this->assertNotEmpty($reminder->render());
    }
}
