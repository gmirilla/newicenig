<?php

use App\Models\FailedJob;
use App\Models\QueuedJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('only lets a super-admin view the queue monitor and job resources', function () {
    Role::findOrCreate('super-admin');
    Role::findOrCreate('admin');

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($superAdmin)->get('/admin/queue-monitor')->assertOk();
    $this->actingAs($superAdmin)->get('/admin/queued-jobs')->assertOk();
    $this->actingAs($superAdmin)->get('/admin/failed-jobs')->assertOk();

    $this->actingAs($admin)->get('/admin/queue-monitor')->assertForbidden();
    $this->actingAs($admin)->get('/admin/queued-jobs')->assertForbidden();
    $this->actingAs($admin)->get('/admin/failed-jobs')->assertForbidden();
});

it('lists pending and failed jobs from the real queue tables', function () {
    Role::findOrCreate('super-admin');
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    QueuedJob::create([
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'App\\Jobs\\ExampleJob']),
        'attempts' => 0,
        'available_at' => now()->timestamp,
        'created_at' => now()->timestamp,
    ]);

    FailedJob::create([
        'uuid' => (string) Str::uuid(),
        'connection' => 'database',
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'App\\Jobs\\ExampleJob']),
        'exception' => "Exception: something broke\n#0 stack trace...",
        'failed_at' => now(),
    ]);

    $this->actingAs($superAdmin)->get('/admin/queued-jobs')->assertOk()->assertSee('ExampleJob');
    $this->actingAs($superAdmin)->get('/admin/failed-jobs')->assertOk()->assertSee('ExampleJob');
});
