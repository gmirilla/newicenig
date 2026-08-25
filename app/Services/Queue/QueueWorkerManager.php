<?php

namespace App\Services\Queue;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;

/**
 * Starts, stops, and reports on a single background `queue:work` process for
 * this application. This suits a small, single-server deployment where the
 * admin panel is the only place the worker is managed from. For a production
 * deployment under real load, a process supervisor (Supervisor, systemd, a
 * Windows service, or Laravel Horizon for Redis queues) is the correct tool —
 * this page is not a replacement for one, only a convenience on top of it
 * being absent.
 */
class QueueWorkerManager
{
    protected const CACHE_KEY = 'queue-worker:pid';

    /**
     * @return array{running: bool, pid: int|null}
     */
    public function status(): array
    {
        $pid = Cache::get(self::CACHE_KEY);

        if (! $pid) {
            return ['running' => false, 'pid' => null];
        }

        if ($this->isRunning((int) $pid)) {
            return ['running' => true, 'pid' => (int) $pid];
        }

        Cache::forget(self::CACHE_KEY);

        return ['running' => false, 'pid' => null];
    }

    public function start(): bool
    {
        if ($this->status()['running']) {
            return false;
        }

        $pid = $this->spawnWorker();

        if (! $pid) {
            return false;
        }

        Cache::forever(self::CACHE_KEY, $pid);

        return true;
    }

    public function stop(): bool
    {
        $status = $this->status();

        if (! $status['running']) {
            return false;
        }

        $this->killProcess($status['pid']);
        Cache::forget(self::CACHE_KEY);

        return true;
    }

    /**
     * Signal any currently running workers to finish their current job and
     * exit gracefully. This is Laravel's built-in mechanism — safe to call
     * regardless of whether the worker tracked by this manager is running.
     */
    public function restart(): void
    {
        Artisan::call('queue:restart');
    }

    protected function spawnWorker(): ?int
    {
        $php = PHP_BINARY;
        $artisan = base_path('artisan');
        $args = 'queue:work --queue=default --tries=3 --sleep=3 --timeout=90';

        if ($this->isWindows()) {
            $result = Process::run(
                "powershell -NoProfile -Command \"(Start-Process -FilePath '{$php}' -ArgumentList '{$artisan} {$args}' -WindowStyle Hidden -PassThru).Id\""
            );

            $pid = (int) trim($result->output());

            return $pid > 0 ? $pid : null;
        }

        $result = Process::run("nohup {$php} {$artisan} {$args} > /dev/null 2>&1 & echo $!");

        $pid = (int) trim($result->output());

        return $pid > 0 ? $pid : null;
    }

    protected function isRunning(int $pid): bool
    {
        if ($this->isWindows()) {
            $result = Process::run("tasklist /FI \"PID eq {$pid}\"");

            return str_contains($result->output(), (string) $pid);
        }

        return Process::run("ps -p {$pid}")->successful();
    }

    protected function killProcess(int $pid): void
    {
        if ($this->isWindows()) {
            Process::run("taskkill /F /T /PID {$pid}");

            return;
        }

        Process::run("kill {$pid}");
    }

    protected function isWindows(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }
}
