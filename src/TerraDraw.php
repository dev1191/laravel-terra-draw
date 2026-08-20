<?php

namespace DevRajThapa\LaravelTerraDraw;

class TerraDraw
{
    protected array $enabledModes;
    protected string $mapStyle;

    public function __construct(array $config = [])
    {
        $this->enabledModes = $config['modes'] ?? [
            'polygon',
            'rectangle',
            'circle',
            'linestring',
            'freehand',
            'point',
            'select',
        ];
        $this->mapStyle = $config['map_style'] ?? 'https://demotiles.maplibre.org/style.json';
    }

    public function modes(): array
    {
        return $this->enabledModes;
    }

    public function mapStyle(): string
    {
        return $this->mapStyle;
    }

    /**
     * Validate that a single decoded GeoJSON feature matches an enabled draw mode.
     */
    public function isValidFeature(array $feature): bool
    {
        $type = $feature['geometry']['type'] ?? null;
        $mode = $feature['properties']['mode'] ?? null;

        $check = strtolower((string) ($mode ?? $type));

        return match ($check) {
            'polygon' => in_array('polygon', $this->enabledModes, true) || in_array('rectangle', $this->enabledModes, true),
            'point' => in_array('point', $this->enabledModes, true),
            'linestring' => in_array('linestring', $this->enabledModes, true) || in_array('freehand', $this->enabledModes, true),
            'freehand' => in_array('freehand', $this->enabledModes, true),
            'circle' => in_array('circle', $this->enabledModes, true),
            'rectangle' => in_array('rectangle', $this->enabledModes, true),
            default => false,
        };
    }

    /**
     * Validate a complete GeoJSON string or array payload.
     */
    public function validate(string|array $geojson, array $allowedModes = []): bool
    {
        $parsed = $this->clean($geojson);
        if (! $parsed) {
            return false;
        }

        $modes = ! empty($allowedModes) ? array_map('strtolower', $allowedModes) : $this->enabledModes;

        $features = $parsed['features'] ?? [];
        if (empty($features)) {
            return false;
        }

        foreach ($features as $feature) {
            $geomType = strtolower((string) ($feature['geometry']['type'] ?? ''));
            $mode = strtolower((string) ($feature['properties']['mode'] ?? $geomType));

            if (! in_array($mode, $modes, true) && ! in_array($geomType, $modes, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the total number of features in a GeoJSON payload.
     */
    public function getFeatureCount(string|array $geojson): int
    {
        $parsed = $this->clean($geojson);

        return $parsed && isset($parsed['features']) ? count($parsed['features']) : 0;
    }

    /**
     * Get unique geometry types present in the GeoJSON.
     */
    public function getGeometryTypes(string|array $geojson): array
    {
        $parsed = $this->clean($geojson);
        if (! $parsed || ! isset($parsed['features'])) {
            return [];
        }

        $types = [];
        foreach ($parsed['features'] as $feature) {
            if (isset($feature['geometry']['type'])) {
                $types[] = $feature['geometry']['type'];
            }
        }

        return array_values(array_unique($types));
    }

    /**
     * Extract all coordinate sets from the GeoJSON.
     */
    public function extractCoordinates(string|array $geojson): array
    {
        $parsed = $this->clean($geojson);
        if (! $parsed || ! isset($parsed['features'])) {
            return [];
        }

        $coords = [];
        foreach ($parsed['features'] as $feature) {
            if (isset($feature['geometry']['coordinates'])) {
                $coords[] = $feature['geometry']['coordinates'];
            }
        }

        return $coords;
    }

    /**
     * Normalize and clean a GeoJSON string/array into a standard FeatureCollection array.
     */
    public function clean(string|array $geojson): ?array
    {
        if (is_string($geojson)) {
            $decoded = json_decode($geojson, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                return null;
            }
            $geojson = $decoded;
        }

        $type = $geojson['type'] ?? null;

        if ($type === 'FeatureCollection') {
            return $geojson;
        }

        if ($type === 'Feature') {
            return [
                'type' => 'FeatureCollection',
                'features' => [$geojson],
            ];
        }

        if (in_array($type, ['Polygon', 'Point', 'LineString', 'MultiPolygon', 'MultiPoint', 'MultiLineString'], true)) {
            return [
                'type' => 'FeatureCollection',
                'features' => [
                    [
                        'type' => 'Feature',
                        'geometry' => $geojson,
                        'properties' => [],
                    ],
                ],
            ];
        }

        return null;
    }

    /**
     * Check if a GeoJSON payload has zero features or is empty.
     */
    public function isEmpty(string|array $geojson): bool
    {
        return $this->getFeatureCount($geojson) === 0;
    }
}
