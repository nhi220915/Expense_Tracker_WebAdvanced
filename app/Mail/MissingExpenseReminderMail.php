<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MissingExpenseReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public int $daysSinceLastExpense
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '💡 Nhắc nhở: Bạn chưa ghi chi tiêu trong ' . $this->daysSinceLastExpense . ' ngày',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.missing-expense-reminder',
            with: [
                'userName' => $this->user->name,
                'daysSince' => $this->daysSinceLastExpense,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
