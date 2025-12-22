<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\MonitorServerMetrics;
use App\Models\User;
use App\Notifications\ServerMetricsAlert;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Console\Application;
use Illuminate\Support\Facades\Artisan;

class MonitorServerMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_does_not_send_alerts_when_metrics_are_ok(): void
    {
        Notification::fake();

        $command = new MonitorServerMetricsWithMockedMetrics([
            'cpu_usage' => 10,
            'memory_usage' => 20,
            'disk_usage' => 30,
            'response_time' => 100,
        ]);

        // Register command instance
        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->registerCommand($command);

        // Call it by signature (defined in parent)
        $this->artisan('monitor:server:test --notify')
            ->assertSuccessful()
            ->expectsOutput('✅ All metrics are within normal thresholds.');

        Notification::assertNothingSent();
    }

    public function test_it_sends_alerts_when_thresholds_exceeded(): void
    {
        Notification::fake();
        // Ensure database has admin
        $admin = User::factory()->create(['email' => 'admin@example.com']);

        $command = new MonitorServerMetricsWithMockedMetrics([
            'cpu_usage' => 90.0,
            'memory_usage' => 90.0,
            'disk_usage' => 90.0,
            'response_time' => 600.0,
        ]);

        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->registerCommand($command);

        $this->artisan('monitor:server:test --notify')
            ->assertFailed(); // Should return FAILURE codes if alerts exist

        Notification::assertSentTo($admin, ServerMetricsAlert::class, function ($notification) use ($admin) {
            return in_array($notification->toArray($admin)['metric'], ['cpu_usage', 'memory_usage', 'disk_usage', 'response_time']);
        });

        Notification::assertSentTimes(ServerMetricsAlert::class, 4);
    }
}

// Subclass to override metrics
class MonitorServerMetricsWithMockedMetrics extends MonitorServerMetrics
{
    protected $signature = 'monitor:server:test {--notify : Send notifications if thresholds are exceeded}';

    protected array $mockedMetrics;

    public function __construct(array $metrics)
    {
        parent::__construct();
        $this->mockedMetrics = $metrics;
    }

    protected function getServerMetrics(): array
    {
        return $this->mockedMetrics;
    }
}

