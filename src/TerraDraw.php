<?php 

namespace DevRajThapa\LaravelTerraDraw;

class TerraDraw
{

    protected array $enabledModes;
    protected string $mapStyle;

    public function __construct(array $config = [])
    {
        $this->enabledModes = $config['modes'] ?? ['polygon', 'point', 'linestring','freehand','circle'];
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
     * Validate that a decoded GeoJSON feature matches an enabled draw mode.
     */
    public function isValidFeature(array $feature): bool
    {
        $type = $feature['geometry']['type'] ?? null;

        return match ($type) {
            'Polygon' => in_array('polygon', $this->enabledModes),
            'Point' => in_array('point', $this->enabledModes),
            'LineString' => in_array('linestring', $this->enabledModes),
            'FreeHand' => in_array('freehand', $this->enabledModes),
            'Circle' => in_array('circle', $this->enabledModes),
            default => false,
        };
    }

}

