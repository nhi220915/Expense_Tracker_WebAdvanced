<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Budget;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BudgetWarningMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public Budget $budget,
        public float $percentageUsed
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $urgency = $this->percentageUsed >= 100 ? 'KHẨN CẤP' : 'Cảnh báo';

        return new Envelope(
            subject: "⚠️ {$urgency}: Ngân sách sắp vượt quá ({$this->percentageUsed}%)",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.budget-warning',
            with: [
                'userName' => $this->user->name,
                'budgetLimit' => number_format($this->budget->limit, 0, ',', '.'),
                'currentSpent' => number_format($this->budget->spent ?? 0, 0, ',', '.'),
                'percentageUsed' => round($this->percentageUsed, 1),
                'remaining' => number_format($this->budget->limit - ($this->budget->spent ?? 0), 0, ',', '.'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
