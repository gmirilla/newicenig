<?php

namespace App\Services\Branding;

use App\Settings\BrandingSettings;
use Illuminate\Support\Facades\Storage;

/**
 * Turns BrandingSettings into what the layouts actually need: resolved
 * asset URLs (falling back to the bundled ICEN default when unconfigured)
 * and the inline CSS variable overrides that re-skin every `bg-brand-*`,
 * `text-accent-*`, etc. utility class already compiled into app.css —
 * without a Tailwind rebuild.
 */
class BrandingPresenter
{
    public function __construct(
        protected BrandingSettings $settings,
    ) {}

    public function logoUrl(): string
    {
        return $this->settings->logo_path
            ? Storage::disk('public')->url($this->settings->logo_path)
            : asset('images/icen-mark.jpg');
    }

    public function faviconUrl(): string
    {
        if ($this->settings->favicon_path) {
            return Storage::disk('public')->url($this->settings->favicon_path);
        }

        return $this->logoUrl();
    }

    /**
     * The inline `:root { ... }` declarations to override the compiled
     * defaults with — safe to emit even when nothing has been customized,
     * since it then just re-states the shipped defaults.
     */
    public function cssVariables(): string
    {
        $declarations = [
            ...$this->rampDeclarations('color-brand', $this->settings->color_brand),
            ...$this->rampDeclarations('color-accent', $this->settings->color_accent),
            '--color-brand-surface: '.$this->settings->color_surface.';',
        ];

        return implode(' ', $declarations);
    }

    /**
     * @return array<int, string>
     */
    protected function rampDeclarations(string $cssVarPrefix, string $baseHex): array
    {
        $declarations = [];

        foreach (ColorRamp::generate($baseHex) as $stop => $hex) {
            $declarations[] = "--{$cssVarPrefix}-{$stop}: {$hex};";
        }

        return $declarations;
    }
}
