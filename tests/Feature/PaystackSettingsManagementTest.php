<?php

use App\Filament\Pages\ManagePaystackSettings;
use App\Models\PaystackSettingsAuditLog;
use App\Models\User;
use App\Settings\PaystackSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('only lets a super-admin view or update paystack settings', function () {
    Role::findOrCreate('super-admin');
    Role::findOrCreate('admin');

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($superAdmin)->get('/admin/manage-paystack-settings')->assertOk();
    $this->actingAs($admin)->get('/admin/manage-paystack-settings')->assertForbidden();
});

it('saves updated paystack credentials and stores them encrypted', function () {
    Role::findOrCreate('super-admin');
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    Livewire::actingAs($admin)
        ->test(ManagePaystackSettings::class)
        ->fillForm([
            'public_key' => 'pk_test_new123',
            'secret_key' => 'sk_test_new456',
            'payment_url' => 'https://api.paystack.co',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = app(PaystackSettings::class);
    expect($settings->public_key)->toBe('pk_test_new123')
        ->and($settings->secret_key)->toBe('sk_test_new456');

    $rawRow = DB::table('settings')->where('group', 'paystack')->where('name', 'secret_key')->first();
    expect($rawRow->payload)->not->toContain('sk_test_new456');
});

it('records an audit log entry when credentials change, without exposing the secret key value', function () {
    Role::findOrCreate('super-admin');
    $admin = User::factory()->create(['name' => 'Ada Admin']);
    $admin->assignRole('super-admin');

    Livewire::actingAs($admin)
        ->test(ManagePaystackSettings::class)
        ->fillForm([
            'public_key' => 'pk_test_audited',
            'secret_key' => 'sk_test_audited',
            'payment_url' => 'https://api.paystack.co',
        ])
        ->call('save');

    $log = PaystackSettingsAuditLog::first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($admin->id)
        ->and($log->changes)->toHaveKey('public_key')
        ->and($log->changes)->toHaveKey('secret_key')
        ->and($log->changes['secret_key'])->toBe(['changed' => true])
        ->and($log->changes['public_key']['to'])->toBe('pk_test_audited');

    expect(json_encode($log->changes))->not->toContain('sk_test_audited');
});

it('does not create an audit log entry when nothing actually changed', function () {
    Role::findOrCreate('super-admin');
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $settings = app(PaystackSettings::class);
    $settings->public_key = 'pk_test_unchanged';
    $settings->secret_key = 'sk_test_unchanged';
    $settings->payment_url = 'https://api.paystack.co';
    $settings->save();

    Livewire::actingAs($admin)
        ->test(ManagePaystackSettings::class)
        ->fillForm([
            'public_key' => 'pk_test_unchanged',
            'secret_key' => 'sk_test_unchanged',
            'payment_url' => 'https://api.paystack.co',
        ])
        ->call('save');

    expect(PaystackSettingsAuditLog::count())->toBe(0);
});
