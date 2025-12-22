<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MonitorRoutesTest extends TestCase
{
    public function test_slack_monitor_route(): void
    {
        Log::shouldReceive('critical')
            ->once()
            ->with('Đây là một thông báo lỗi thử nghiệm từ Laravel!');

        $response = $this->get('/test-slack');

        $response->assertOk();
        $response->assertSee('Đã gửi thông báo về Slack!');
    }

    public function test_monitor_exception_route(): void
    {
        Log::shouldReceive('critical')
            ->once()
            ->with('Hệ thống giám sát: Phát hiện lỗi thử nghiệm!');

        // Laravel's exception handler logs errors, so we must allow it
        Log::shouldReceive('error')->withAnyArgs();

        $this->withoutExceptionHandling();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Sentry Test Error');

        $this->get('/test-monitor');
    }
}
