<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

it('pings the configured heartbeat url', function () {
    config(['services.scheduler_heartbeat.url' => 'https://hc-ping.com/test-check']);
    Http::fake();

    $this->artisan('app:ping-scheduler-heartbeat')->assertSuccessful();

    Http::assertSent(fn ($request) => $request->url() === 'https://hc-ping.com/test-check');
});

it('does nothing when no heartbeat url is configured', function () {
    config(['services.scheduler_heartbeat.url' => null]);
    Http::fake();

    $this->artisan('app:ping-scheduler-heartbeat')->assertSuccessful();

    Http::assertNothingSent();
});

it('does not fail the command when the ping itself throws', function () {
    config(['services.scheduler_heartbeat.url' => 'https://hc-ping.com/test-check']);
    Http::fake(fn () => throw new ConnectionException('Connection timed out'));

    $this->artisan('app:ping-scheduler-heartbeat')->assertSuccessful();
});
