<?php

use App\Services\Queue\QueueWorkerManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;

uses(RefreshDatabase::class);

it('reports stopped when no worker has ever been started', function () {
    $manager = app(QueueWorkerManager::class);

    expect($manager->status())->toBe(['running' => false, 'pid' => null]);
});

it('starts a worker, tracks its pid, and reports it as running', function () {
    Process::fake([
        '*' => Process::result(output: '4242'),
    ]);

    $manager = app(QueueWorkerManager::class);

    expect($manager->start())->toBeTrue();

    $status = $manager->status();
    expect($status['running'])->toBeTrue()
        ->and($status['pid'])->toBe(4242);
});

it('does not start a second worker when one is already tracked as running', function () {
    Process::fake([
        '*' => Process::result(output: '4242'),
    ]);

    $manager = app(QueueWorkerManager::class);
    $manager->start();

    expect($manager->start())->toBeFalse();
});

it('reports stopped and clears the cache when the tracked pid is no longer alive', function () {
    Cache::forever('queue-worker:pid', 9999);

    // Any liveness-check process call reports failure (process not found).
    Process::fake([
        '*' => Process::result(output: '', errorOutput: '', exitCode: 1),
    ]);

    $manager = app(QueueWorkerManager::class);

    expect($manager->status())->toBe(['running' => false, 'pid' => null]);
    expect(Cache::get('queue-worker:pid'))->toBeNull();
});

it('stops a running worker and clears the tracked pid', function () {
    Process::fake([
        '*' => Process::result(output: '4242'),
    ]);

    $manager = app(QueueWorkerManager::class);
    $manager->start();

    expect($manager->stop())->toBeTrue();
    expect(Cache::get('queue-worker:pid'))->toBeNull();
});

it('does nothing when stopping and no worker is running', function () {
    $manager = app(QueueWorkerManager::class);

    expect($manager->stop())->toBeFalse();
});

it('sends the queue:restart signal', function () {
    Artisan::shouldReceive('call')->once()->with('queue:restart');

    app(QueueWorkerManager::class)->restart();
});
