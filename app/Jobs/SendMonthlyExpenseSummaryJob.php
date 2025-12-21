<?php

namespace App\Jobs;

use App\Mail\MonthlyExpenseSummaryMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class SendMonthlyExpenseSummaryJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $timeout = 120;
    public $deleteWhenMissingModels = true;

    public function __construct(
        public User $user
    ) {
        $this->onQueue('emails');
    }

    public function handle(): void
    {
        try {
            // Calculate monthly expenses
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();

            $expenses = $this->user->expenses()
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->get();

            $totalSpent = $expenses->sum('amount');
            $byCategory = $expenses->groupBy('expense_category_id')
                ->map(function ($items) {
                    return [
                        'category' => $items->first()->expenseCategory->name ?? 'Uncategorized',
                        'total' => $items->sum('amount'),
                        'count' => $items->count(),
                    ];
                });

            Mail::to($this->user->email)
                ->send(new MonthlyExpenseSummaryMail(
                    $this->user,
                    $totalSpent,
                    $byCategory->toArray()
                ));

            Log::info('Monthly expense summary sent', [
                'user_id' => $this->user->id,
                'total_spent' => $totalSpent,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send monthly summary', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(?Exception $exception): void
    {
        Log::critical('Monthly summary job failed permanently', [
            'user_id' => $this->user->id,
            'error' => $exception?->getMessage(),
        ]);
    }
}
