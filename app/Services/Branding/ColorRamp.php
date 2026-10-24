<?php

namespace App\Services\Branding;

/**
 * Generates a full Tailwind-style 50–950 color scale from a single base hex,
 * so a Branding settings page can offer one color picker per role (brand,
 * accent) instead of eleven. The base color is anchored at stop 500 — the
 * hex comes back out unchanged there — and every other stop is reached by
 * shifting lightness (holding hue and saturation fixed) by a fixed offset
 * calibrated from this app's original, hand-picked brand-green ramp.
 *
 * This is a deliberate approximation: a real designer-picked palette often
 * drifts in hue and saturation between stops too, which this doesn't
 * reproduce. It's visually coherent, not pixel-identical to a bespoke ramp.
 */
class ColorRamp
{
    /**
     * Lightness offset from stop 500, in HSL percentage points, calibrated
     * against the original brand-green scale in resources/css/app.css.
     *
     * @var array<int, float>
     */
    private const LIGHTNESS_OFFSETS = [
        50 => 60.4,
        100 => 56.2,
        200 => 48.8,
        300 => 36.8,
        400 => 21.7,
        500 => 0.0,
        600 => -7.1,
        700 => -12.2,
        800 => -16.1,
        900 => -21.4,
        950 => -26.3,
    ];

    /**
     * @return array<int, string> Stop => hex, e.g. [50 => '#f0fdf4', ..., 500 => $baseHex, ...].
     */
    public static function generate(string $baseHex): array
    {
        [$hue, $saturation, $lightness] = self::hexToHsl($baseHex);

        $ramp = [];

        foreach (self::LIGHTNESS_OFFSETS as $stop => $offset) {
            $ramp[$stop] = $stop === 500
                ? self::normalizeHex($baseHex)
                : self::hslToHex($hue, $saturation, max(0, min(100, $lightness + $offset)));
        }

        return $ramp;
    }

    private static function normalizeHex(string $hex): string
    {
        return '#'.strtolower(ltrim($hex, '#'));
    }

    /**
     * @return array{0: float, 1: float, 2: float} [hue 0-360, saturation 0-100, lightness 0-100]
     */
    private static function hexToHsl(string $hex): array
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        $d = $max - $min;

        if ($d == 0.0) {
            return [0.0, 0.0, $l * 100];
        }

        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

        $h = match ($max) {
            $r => fmod(($g - $b) / $d, 6),
            $g => ($b - $r) / $d + 2,
            default => ($r - $g) / $d + 4,
        } * 60;

        return [$h < 0 ? $h + 360 : $h, $s * 100, $l * 100];
    }

    private static function hslToHex(float $h, float $s, float $l): string
    {
        $s /= 100;
        $l /= 100;

        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        $toByte = fn (float $v) => str_pad(dechex((int) round(($v + $m) * 255)), 2, '0', STR_PAD_LEFT);

        return '#'.$toByte($r).$toByte($g).$toByte($b);
    }
}
