<?php

namespace Tests\Unit\Notifications;

use App\Notifications\ServerMetricsAlert;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ServerMetricsAlertTest extends TestCase
{
    public function test_via_returns_correct_channels(): void
    {
        Config::set('logging.channels.slack.url', 'https://hooks.slack.com/services/...');

        $notification = new ServerMetricsAlert('cpu', 90, 80);
        $notifiable = new \stdClass;

        $channels = $notification->via($notifiable);

        $this->assertContains('mail', $channels);
        $this->assertContains('slack', $channels);
    }

    public function test_to_mail_returns_correct_message(): void
    {
        $notification = new ServerMetricsAlert('cpu_usage', 90.5, 80.0, 'critical');
        $notifiable = new \stdClass;

        $mail = $notification->toMail($notifiable);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertEquals('error', $mail->level);
        $this->assertStringContainsString('Server Metric Alert: cpu_usage', $mail->subject);
        $this->assertStringContainsString('Alert!', $mail->greeting);
    }

    /*
    public function test_to_slack_returns_correct_message(): void
    {
        $notification = new ServerMetricsAlert('memory_usage', 95.0, 85.0, 'critical');
        $notifiable = new \stdClass;

        $slack = $notification->toSlack($notifiable);

        $this->assertInstanceOf(SlackMessage::class, $slack);
        $this->assertEquals('danger', $slack->attachments[0]->color);
        $this->assertStringContainsString('Server Metric Alert', $slack->content);
    }
    */

    public function test_array_representation(): void
    {
        $notification = new ServerMetricsAlert('disk_usage', 90.0, 85.0);
        $notifiable = new \stdClass;

        $data = $notification->toArray($notifiable);

        $this->assertEquals('disk_usage', $data['metric']);
        $this->assertEquals(90.0, $data['value']);
        $this->assertArrayHasKey('formatted_value', $data);
    }
}
