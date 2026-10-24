@props([])

@php
    $branding = app(\App\Services\Branding\BrandingPresenter::class);
@endphp

<img src="{{ $branding->logoUrl() }}" alt="{{ app(\App\Settings\BrandingSettings::class)->brand_short_name }}" {{ $attributes->class(['object-cover']) }}>
