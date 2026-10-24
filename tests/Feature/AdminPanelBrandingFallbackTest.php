<?php

use App\Providers\Filament\AdminPanelProvider;
use App\Settings\BrandingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

/**
 * resolveBranding() is intentionally protected — it's not part of the
 * provider's public API — so tests reach it via reflection rather than
 * widening its visibility just to be testable.
 */
function resolveAdminPanelBranding(): array
{
    $provider = new AdminPanelProvider(app());
    $method = new ReflectionMethod($provider, 'resolveBranding');
    $method->setAccessible(true);

    return $method->invoke($provider);
}

/**
 * Regression test for a real bug: AdminPanelProvider::panel() is invoked by
 * Filament on every framework boot, including `php artisan migrate` on a
 * brand-new database — before the `settings` table it reads from exists.
 * Without a fallback, that makes it impossible to ever run the first
 * migration on a fresh install.
 */
it('falls back to the literal defaults when the settings table is unavailable', function () {
    Schema::dropIfExists('settings');

    $branding = resolveAdminPanelBranding();

    expect($branding)->toBe([
        'short_name' => 'ICEN',
        'logo_url' => asset('images/icen-mark.jpg'),
        'favicon_url' => asset('images/icen-mark.jpg'),
        'color' => '#16a34a',
    ]);
});

it('uses the configured branding settings when they are available', function () {
    app(BrandingSettings::class)->fill([
        'brand_short_name' => 'ACME',
        'color_brand' => '#123456',
    ])->save();

    $branding = resolveAdminPanelBranding();

    expect($branding['short_name'])->toBe('ACME');
    expect($branding['color'])->toBe('#123456');
});
