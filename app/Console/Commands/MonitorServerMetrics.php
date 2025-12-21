<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\ServerMetricsAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class MonitorServerMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:server
                            {--notify : Send notifications if thresholds are exceeded}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor server metrics and alert if thresholds are exceeded';

    /**
     * Thresholds for alerts
     */
    protected array $thresholds = [
        'cpu_usage' => 80.0,        // Alert if CPU > 80%
        'memory_usage' => 85.0,      // Alert if Memory > 85%
        'disk_usage' => 85.0,        // Alert if Disk > 85% (< 15% free)
        'response_time' => 500.0,    // Alert if avg response > 500ms
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Monitoring server metrics...');
        $this->newLine();

        $metrics = $this->getServerMetrics();
        $alerts = [];

        // Check each metric against thresholds
        foreach ($metrics as $metric => $value) {
            $threshold = $this->thresholds[$metric] ?? null;

            if ($threshold && $value > $threshold) {
                $severity = $value > ($threshold + 10) ? 'critical' : 'warning';
                $alerts[] = [
                    'metric' => $metric,
                    'value' => $value,
                    'threshold' => $threshold,
                    'severity' => $severity,
                ];

                $emoji = $severity === 'critical' ? '🚨' : '⚠️';
                $unit = ($metric === 'response_time') ? 'ms' : '%';
                $this->error("{$emoji} {$metric}: {$value}{$unit} (threshold: {$threshold}{$unit})");
            } else {
                $unit = ($metric === 'response_time') ? 'ms' : '%';
                $this->info("✅ {$metric}: {$value}{$unit}" . ($threshold ? " (threshold: {$threshold}{$unit})" : ""));
            }
        }

        $this->newLine();

        // Send notifications if there are alerts and --notify flag is set
        if (!empty($alerts) && $this->option('notify')) {
            $this->sendAlerts($alerts);
        }

        // Summary
        if (empty($alerts)) {
            $this->info('✅ All metrics are within normal thresholds.');
        } else {
            $this->warn('⚠️  ' . count($alerts) . ' metric(s) exceeded threshold.');
        }

        return empty($alerts) ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Get current server metrics
     */
    protected function getServerMetrics(): array
    {
        $metrics = [];

        // CPU Usage (Linux/Unix)
        if (PHP_OS_FAMILY === 'Linux' || PHP_OS_FAMILY === 'Darwin') {
            $cpuUsage = $this->getCpuUsageLinux();
        } else {
            // Windows fallback
            $cpuUsage = $this->getCpuUsageWindows();
        }
        $metrics['cpu_usage'] = $cpuUsage;

        // Memory Usage
        $memoryUsage = $this->getMemoryUsage();
        $metrics['memory_usage'] = $memoryUsage;

        // Disk Usage
        $diskUsage = $this->getDiskUsage();
        $metrics['disk_usage'] = $diskUsage;

        // Average Response Time (from Pulse if available)
        $avgResponseTime = $this->getAverageResponseTime();
        if ($avgResponseTime !== null) {
            $metrics['response_time'] = $avgResponseTime;
        }

        return $metrics;
    }

    /**
     * Get CPU usage on Linux/Unix
     */
    protected function getCpuUsageLinux(): float
    {
        if (!file_exists('/proc/stat')) {
            return 0.0;
        }

        $stat1 = file('/proc/stat');
        sleep(1);
        $stat2 = file('/proc/stat');

        $info1 = explode(" ", preg_replace("!cpu +!", "", $stat1[0]));
        $info2 = explode(" ", preg_replace("!cpu +!", "", $stat2[0]));

        $dif = [];
        $dif['user'] = $info2[0] - $info1[0];
        $dif['nice'] = $info2[1] - $info1[1];
        $dif['sys'] = $info2[2] - $info1[2];
        $dif['idle'] = $info2[3] - $info1[3];

        $total = array_sum($dif);
        $cpu = $total > 0 ? (($total - $dif['idle']) / $total) * 100 : 0;

        return round($cpu, 2);
    }

    /**
     * Get CPU usage on Windows (approximation)
     */
    protected function getCpuUsageWindows(): float
    {
        // Windows doesn't easily expose CPU usage
        // Return 0 or use WMI if available
        return 0.0;
    }

    /**
     * Get memory usage percentage
     */
    protected function getMemoryUsage(): float
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $meminfo = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+)/', $meminfo, $totalMatch);
            preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $availableMatch);

            if ($totalMatch && $availableMatch) {
                $total = (int) $totalMatch[1];
                $available = (int) $availableMatch[1];
                $used = $total - $available;
                return round(($used / $total) * 100, 2);
            }
        } elseif (PHP_OS_FAMILY === 'Windows') {
            // Fallback for Windows
            $limit = (int) ini_get('memory_limit');
            if ($limit === -1) {
                return 0.0;
            }
            $usage = memory_get_usage(true);
            return round(($usage / ($limit * 1024 * 1024)) * 100, 2);
        }

        // Fallback: PHP memory usage as percentage of limit
        $usage = memory_get_usage(true);
        $limit = $this->parseMemoryLimit(ini_get('memory_limit'));
        return $limit > 0 ? round(($usage / $limit) * 100, 2) : 0.0;
    }

    /**
     * Get disk usage percentage
     */
    protected function getDiskUsage(): float
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');

        if ($total > 0) {
            $used = $total - $free;
            return round(($used / $total) * 100, 2);
        }

        return 0.0;
    }

    /**
     * Get average response time from database (last hour)
     */
    protected function getAverageResponseTime(): ?float
    {
        try {
            // Query Pulse slow_requests table if it exists
            $avg = DB::table('pulse_entries')
                ->where('type', 'slow_request')
                ->where('timestamp', '>=', now()->subHour()->timestamp)
                ->avg('value');

            return $avg ? round((float) $avg, 2) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse memory limit string to bytes
     */
    protected function parseMemoryLimit(string $limit): int
    {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int) $limit;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Send alert notifications
     */
    protected function sendAlerts(array $alerts): void
    {
        // Get admin users (you can customize this)
        $admins = User::where('email', 'like', '%admin%')
            ->orWhere('id', 1) // First user
            ->get();

        if ($admins->isEmpty()) {
            $this->comment('No admin users found to notify.');
            return;
        }

        foreach ($alerts as $alert) {
            Notification::send(
                $admins,
                new ServerMetricsAlert(
                    $alert['metric'],
                    $alert['value'],
                    $alert['threshold'],
                    $alert['severity']
                )
            );

            $this->comment("📧 Alert sent for {$alert['metric']}");
        }
    }
}
