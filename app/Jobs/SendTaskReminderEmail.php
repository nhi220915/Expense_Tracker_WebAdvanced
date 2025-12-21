<?php
namespace App\Jobs;

use App\Mail\TaskReminderMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTaskReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Thử lại tối đa 3 lần nếu lỗi
    public $backoff = 60; // Chờ 60 giây trước khi thử lại

    public function __construct(protected $user, protected $data) {}

    public function handle(): void
    {
        Mail::to($this->user->email)->send(new TaskReminderMail(
            $this->user, 
            $this->data['expenses'], 
            $this->data['budgets']
        ));
    }
}