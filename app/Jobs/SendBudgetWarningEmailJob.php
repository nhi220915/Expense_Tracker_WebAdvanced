<?php

namespace App\Jobs;

use App\Mail\BudgetWarningMail;
use App\Models\User;
use App\Models\Budget;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class SendBudgetWarningEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 60;

    /**
     * Delete the job if its models no longer exist.
     */
    public $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public Budget $budget,
        public float $percentageUsed
    ) {
        // Set queue name for prioritization
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Sending budget warning email', [
                'user_id' => $this->user->id,
                'budget_id' => $this->budget->id,
                'percentage_used' => $this->percentageUsed,
            ]);

            Mail::to($this->user->email)
                ->send(new BudgetWarningMail(
                    $this->user,
                    $this->budget,
                    $this->percentageUsed
                ));

            Log::info('Budget warning email sent successfully', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send budget warning email', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
                'attempts' => $this->attempts(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Exception $exception): void
    {
        Log::critical('Budget warning email job failed permanently', [
            'user_id' => $this->user->id,
            'budget_id' => $this->budget->id,
            'error' => $exception?->getMessage(),
        ]);

        // TODO: Notify admin about critical failure
    }
}
