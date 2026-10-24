<?php

use App\Services\Branding\ColorRamp;

it('anchors the base hex exactly at stop 500', function () {
    $ramp = ColorRamp::generate('#16a34a');

    expect($ramp[500])->toBe('#16a34a');
});

it('generates all eleven tailwind stops', function () {
    $ramp = ColorRamp::generate('#f59e0b');

    expect(array_keys($ramp))->toBe([50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950]);
});

it('gets lighter toward 50 and darker toward 950', function () {
    $ramp = ColorRamp::generate('#16a34a');

    $lightnessOf = function (string $hex) {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return (max($r, $g, $b) + min($r, $g, $b)) / 2;
    };

    expect($lightnessOf($ramp[50]))->toBeGreaterThan($lightnessOf($ramp[500]));
    expect($lightnessOf($ramp[950]))->toBeLessThan($lightnessOf($ramp[500]));
});

it('normalizes an uppercase or unprefixed hex', function () {
    expect(ColorRamp::generate('16A34A')[500])->toBe('#16a34a');
});
