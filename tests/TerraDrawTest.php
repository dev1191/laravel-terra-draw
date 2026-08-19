<?php

use DevRajThapa\LaravelTerraDraw\TerraDraw;

it('validates a polygon feature when freehand mode is enabled', function () {
    $terraDraw = new TerraDraw(['modes' => ['freehand']]);

    $feature = ['geometry' => ['type' => 'FreeHand']];

    expect($terraDraw->isValidFeature($feature))->toBeTrue();
});

it('rejects a point feature when point mode is disabled', function () {
    $terraDraw = new TerraDraw(['modes' => ['polygon']]);

    $feature = ['geometry' => ['type' => 'Point']];

    expect($terraDraw->isValidFeature($feature))->toBeFalse();
});

it('has default config values', function () {
    expect(config('terra-draw.modes'))->toBe(['polygon', 'point', 'linestring', 'freehand', 'circle']);
});
