<?php

use DevRajThapa\LaravelTerraDraw\Facades\TerraDraw;

$sampleCollection = [
    'type' => 'FeatureCollection',
    'features' => [
        [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [
                    [
                        [85.315, 27.710],
                        [85.335, 27.710],
                        [85.335, 27.725],
                        [85.315, 27.725],
                        [85.315, 27.710],
                    ],
                ],
            ],
            'properties' => ['mode' => 'polygon'],
        ],
        [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [85.3240, 27.7172],
            ],
            'properties' => ['mode' => 'point'],
        ],
    ],
];

it('counts features accurately via TerraDraw service', function () use ($sampleCollection) {
    expect(TerraDraw::getFeatureCount($sampleCollection))->toBe(2)
        ->and(TerraDraw::getFeatureCount(json_encode($sampleCollection)))->toBe(2)
        ->and(TerraDraw::getFeatureCount([]))->toBe(0);
});

it('extracts geometry types via TerraDraw service', function () use ($sampleCollection) {
    $types = TerraDraw::getGeometryTypes($sampleCollection);

    expect($types)->toContain('Polygon', 'Point');
});

it('extracts coordinate collections via TerraDraw service', function () use ($sampleCollection) {
    $coords = TerraDraw::extractCoordinates($sampleCollection);

    expect($coords)->toBeArray()
        ->and(count($coords))->toBe(2);
});

it('validates GeoJSON modes accurately', function () use ($sampleCollection) {
    expect(TerraDraw::validate($sampleCollection, ['polygon', 'point']))->toBeTrue()
        ->and(TerraDraw::validate($sampleCollection, ['linestring']))->toBeFalse();
});

it('identifies empty vs non-empty GeoJSON payloads', function () use ($sampleCollection) {
    expect(TerraDraw::isEmpty($sampleCollection))->toBeFalse()
        ->and(TerraDraw::isEmpty(['type' => 'FeatureCollection', 'features' => []]))->toBeTrue();
});
