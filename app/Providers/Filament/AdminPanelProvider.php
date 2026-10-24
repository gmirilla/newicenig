<?php

namespace App\Providers\Filament;

use App\Services\Branding\BrandingPresenter;
use App\Settings\BrandingSettings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // Filament boots every registered panel as part of booting the
        // framework itself — including for `php artisan migrate` on a
        // brand-new database, before the `settings` table this depends on
        // exists. Falling back to the original literal values on any
        // failure (missing table, a settings migration not yet run, ...)
        // keeps that bootstrap-time path working regardless; real requests
        // get the configured branding as normal once migrations have run.
        $branding = $this->resolveBranding();

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName($branding['short_name'])
            ->brandLogo($branding['logo_url'])
            ->favicon($branding['favicon_url'])
            ->colors([
                'primary' => Color::hex($branding['color']),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    /**
     * @return array{short_name: string, logo_url: string, favicon_url: string, color: string}
     */
    protected function resolveBranding(): array
    {
        try {
            $branding = app(BrandingSettings::class);
            $presenter = app(BrandingPresenter::class);

            return [
                'short_name' => $branding->brand_short_name,
                'logo_url' => $presenter->logoUrl(),
                'favicon_url' => $presenter->faviconUrl(),
                'color' => $branding->color_brand,
            ];
        } catch (\Throwable) {
            return [
                'short_name' => 'ICEN',
                'logo_url' => asset('images/icen-mark.jpg'),
                'favicon_url' => asset('images/icen-mark.jpg'),
                'color' => '#16a34a',
            ];
        }
    }
}
