<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class ServerMetricsAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $metric;
    protected mixed $value;
    protected mixed $threshold;
    protected string $severity;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $metric, mixed $value, mixed $threshold, string $severity = 'warning')
    {
        $this->metric = $metric;
        $this->value = $value;
        $this->threshold = $threshold;
        $this->severity = $severity;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail'];

        // Add Slack if configured
        if (config('logging.channels.slack.url')) {
            $channels[] = 'slack';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $emoji = $this->severity === 'critical' ? '🚨' : '⚠️';
        $color = $this->severity === 'critical' ? 'error' : 'warning';

        return (new MailMessage)
            ->subject("{$emoji} Server Metric Alert: {$this->metric}")
            ->level($color)
            ->greeting("Server Metric Alert!")
            ->line("**Metric:** {$this->metric}")
            ->line("**Current Value:** {$this->formatValue($this->value)}")
            ->line("**Threshold:** {$this->formatValue($this->threshold)}")
            ->line("**Severity:** " . strtoupper($this->severity))
            ->line("**Time:** " . now()->toDateTimeString())
            ->action('View Pulse Dashboard', url('/pulse'))
            ->line('Please investigate this issue as soon as possible.');
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(object $notifiable): SlackMessage
    {
        $emoji = $this->severity === 'critical' ? ':rotating_light:' : ':warning:';
        $color = $this->severity === 'critical' ? 'danger' : 'warning';

        return (new SlackMessage)
            ->from('Expense Tracker Monitor', ':chart_with_upwards_trend:')
            ->to(config('logging.channels.slack.channel', '#alerts'))
            ->content("{$emoji} **Server Metric Alert**: {$this->metric}")
            ->attachment(function ($attachment) use ($color) {
                $attachment->title('Alert Details')
                    ->color($color)
                    ->fields([
                        'Metric' => $this->metric,
                        'Current Value' => $this->formatValue($this->value),
                        'Threshold' => $this->formatValue($this->threshold),
                        'Severity' => strtoupper($this->severity),
                        'Server' => gethostname(),
                        'Time' => now()->toDateTimeString(),
                    ])
                    ->action('View Dashboard', url('/pulse'));
            });
    }

    /**
     * Format value for display
     */
    protected function formatValue(mixed $value): string
    {
        if (is_numeric($value)) {
            // If it's a percentage or small decimal, format appropriately
            if ($value <= 1 && $value >= 0) {
                return round($value * 100, 2) . '%';
            }
            // For memory/disk in bytes, convert to human-readable
            if ($value > 1024 * 1024) {
                return $this->formatBytes($value);
            }
            return number_format($value, 2);
        }

        return (string) $value;
    }

    /**
     * Convert bytes to human-readable format
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'metric' => $this->metric,
            'value' => $this->value,
            'threshold' => $this->threshold,
            'severity' => $this->severity,
            'formatted_value' => $this->formatValue($this->value),
            'server' => gethostname(),
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
