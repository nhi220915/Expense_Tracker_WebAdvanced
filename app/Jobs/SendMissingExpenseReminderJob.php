<?php

namespace App\Jobs;

use App\Mail\MissingExpenseReminderMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class SendMissingExpenseReminderJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $timeout = 60;
    public $deleteWhenMissingModels = true;

    public function __construct(
        public User $user,
        public int $daysSinceLastExpense
    ) {
        $this->onQueue('emails');
    }

    public function handle(): void
    {
        try {
            Log::info('Sending missing expense reminder', [
                'user_id' => $this->user->id,
                'days_since_last' => $this->daysSinceLastExpense,
            ]);

            Mail::to($this->user->email)
                ->send(new MissingExpenseReminderMail(
                    $this->user,
                    $this->daysSinceLastExpense
                ));

            Log::info('Missing expense reminder sent successfully', [
                'user_id' => $this->user->id,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send missing expense reminder', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(?Exception $exception): void
    {
        Log::critical('Missing expense reminder job failed permanently', [
            'user_id' => $this->user->id,
            'error' => $exception?->getMessage(),
        ]);
    }
}
