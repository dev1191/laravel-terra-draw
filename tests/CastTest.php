<?php

use DevRajThapa\LaravelTerraDraw\Casts\AsGeoJson;
use Illuminate\Database\Eloquent\Model;

class TestLocationModel extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'geometry' => AsGeoJson::class,
        ];
    }
}

it('casts a GeoJSON json string to array when accessing attribute', function () {
    $rawGeoJson = json_encode([
        'type' => 'Point',
        'coordinates' => [85.32, 27.71],
    ]);

    $model = new TestLocationModel;
    $model->setRawAttributes(['geometry' => $rawGeoJson]);

    expect($model->geometry)->toBeArray()
        ->and($model->geometry['type'])->toBe('Point')
        ->and($model->geometry['coordinates'])->toBe([85.32, 27.71]);
});

it('casts an array to json string when mutating attribute', function () {
    $model = new TestLocationModel;
    $model->geometry = [
        'type' => 'Point',
        'coordinates' => [85.32, 27.71],
    ];

    $raw = $model->getAttributes()['geometry'];

    expect($raw)->toBeString()
        ->and(json_decode($raw, true))->toBe([
            'type' => 'Point',
            'coordinates' => [85.32, 27.71],
        ]);
});

it('handles null values correctly in AsGeoJson cast', function () {
    $model = new TestLocationModel;
    $model->geometry = null;

    expect($model->geometry)->toBeNull()
        ->and($model->getAttributes()['geometry'] ?? null)->toBeNull();
});
