<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MonthlyExpenseSummaryMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public float $totalSpent,
        public array $byCategory
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📊 Tổng kết chi tiêu tháng ' . now()->format('m/Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.monthly-summary',
            with: [
                'userName' => $this->user->name,
                'month' => now()->format('m/Y'),
                'totalSpent' => number_format($this->totalSpent, 0, ',', '.'),
                'categories' => $this->byCategory,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
