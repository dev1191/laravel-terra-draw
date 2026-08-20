<?php

namespace DevRajThapa\LaravelTerraDraw\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array modes()
 * @method static string mapStyle()
 * @method static bool isValidFeature(array $feature)
 * @method static bool validate(string|array $geojson, array $allowedModes = [])
 * @method static int getFeatureCount(string|array $geojson)
 * @method static array getGeometryTypes(string|array $geojson)
 * @method static array extractCoordinates(string|array $geojson)
 * @method static ?array clean(string|array $geojson)
 * @method static bool isEmpty(string|array $geojson)
 *
 * @see \DevRajThapa\LaravelTerraDraw\TerraDraw
 */
class TerraDraw extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \DevRajThapa\LaravelTerraDraw\TerraDraw::class;
    }
}
