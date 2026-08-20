<?php

use DevRajThapa\LaravelTerraDraw\Rules\ValidGeoJson;
use Illuminate\Support\Facades\Validator;

$validPolygon = json_encode([
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
    ],
]);

$validPoint = json_encode([
    'type' => 'FeatureCollection',
    'features' => [
        [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [85.3240, 27.7172],
            ],
            'properties' => ['mode' => 'point'],
        ],
    ],
]);

$invalidCoords = json_encode([
    'type' => 'FeatureCollection',
    'features' => [
        [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [250.0, 999.0], // Out of range!
            ],
            'properties' => ['mode' => 'point'],
        ],
    ],
]);

it('passes validation for a valid GeoJSON FeatureCollection', function () use ($validPolygon) {
    $validator = Validator::make(
        ['boundary' => $validPolygon],
        ['boundary' => ['required', new ValidGeoJson]]
    );

    expect($validator->passes())->toBeTrue();
});

it('fails validation for invalid JSON string', function () {
    $validator = Validator::make(
        ['boundary' => '{ invalid json string ...'],
        ['boundary' => ['required', new ValidGeoJson]]
    );

    expect($validator->fails())->toBeTrue();
});

it('fails validation when coordinates exceed valid latitude/longitude bounds', function () use ($invalidCoords) {
    $validator = Validator::make(
        ['boundary' => $invalidCoords],
        ['boundary' => ['required', new ValidGeoJson]]
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('boundary'))->toContain('invalid coordinate values');
});

it('enforces onlyPolygons fluent constraint', function () use ($validPolygon, $validPoint) {
    $polyValidator = Validator::make(
        ['boundary' => $validPolygon],
        ['boundary' => [ValidGeoJson::make()->onlyPolygons()]]
    );
    expect($polyValidator->passes())->toBeTrue();

    $pointValidator = Validator::make(
        ['boundary' => $validPoint],
        ['boundary' => [ValidGeoJson::make()->onlyPolygons()]]
    );
    expect($pointValidator->fails())->toBeTrue()
        ->and($pointValidator->errors()->first('boundary'))->toContain('unpermitted mode');
});

it('enforces onlyPoints fluent constraint', function () use ($validPolygon, $validPoint) {
    $pointValidator = Validator::make(
        ['location' => $validPoint],
        ['location' => [ValidGeoJson::make()->onlyPoints()]]
    );
    expect($pointValidator->passes())->toBeTrue();

    $polyValidator = Validator::make(
        ['location' => $validPolygon],
        ['location' => [ValidGeoJson::make()->onlyPoints()]]
    );
    expect($polyValidator->fails())->toBeTrue();
});

it('enforces min and max feature count constraints', function () use ($validPolygon) {
    $validatorMin = Validator::make(
        ['boundary' => $validPolygon],
        ['boundary' => [ValidGeoJson::make()->minFeatures(2)]]
    );
    expect($validatorMin->fails())->toBeTrue()
        ->and($validatorMin->errors()->first('boundary'))->toContain('must contain at least 2 feature(s)');

    $validatorMax = Validator::make(
        ['boundary' => $validPolygon],
        ['boundary' => [ValidGeoJson::make()->maxFeatures(1)]]
    );
    expect($validatorMax->passes())->toBeTrue();
});
