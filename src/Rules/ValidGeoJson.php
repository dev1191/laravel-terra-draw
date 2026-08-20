<?php

namespace DevRajThapa\LaravelTerraDraw\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidGeoJson implements ValidationRule
{
    protected ?array $allowedModes = null;

    protected ?int $minFeatures = null;

    protected ?int $maxFeatures = null;

    protected bool $allowEmpty = false;

    public function __construct(array $allowedModes = [])
    {
        if (! empty($allowedModes)) {
            $this->allowedModes = array_map('strtolower', $allowedModes);
        }
    }

    public static function make(array $allowedModes = []): static
    {
        return new static($allowedModes);
    }

    public function onlyPolygons(): static
    {
        $this->allowedModes = ['polygon', 'rectangle'];

        return $this;
    }

    public function onlyPoints(): static
    {
        $this->allowedModes = ['point'];

        return $this;
    }

    public function onlyLineStrings(): static
    {
        $this->allowedModes = ['linestring', 'freehand'];

        return $this;
    }

    public function allowedModes(array $modes): static
    {
        $this->allowedModes = array_map('strtolower', $modes);

        return $this;
    }

    public function minFeatures(int $min): static
    {
        $this->minFeatures = $min;

        return $this;
    }

    public function maxFeatures(int $max): static
    {
        $this->maxFeatures = $max;

        return $this;
    }

    public function allowEmpty(bool $allow = true): static
    {
        $this->allowEmpty = $allow;

        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($value) || $value === '') {
            if (! $this->allowEmpty) {
                $fail("The {$attribute} must be a valid GeoJSON payload.");
            }

            return;
        }

        $decoded = is_string($value) ? json_decode($value, true) : $value;

        if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            $fail("The {$attribute} must be a valid JSON/GeoJSON structure.");

            return;
        }

        $type = $decoded['type'] ?? null;

        if (! in_array($type, ['FeatureCollection', 'Feature', 'Polygon', 'MultiPolygon', 'Point', 'MultiPoint', 'LineString', 'MultiLineString', 'GeometryCollection'], true)) {
            $fail("The {$attribute} does not contain a valid GeoJSON type.");

            return;
        }

        $features = [];

        if ($type === 'FeatureCollection') {
            if (! isset($decoded['features']) || ! is_array($decoded['features'])) {
                $fail("The {$attribute} FeatureCollection must contain a features array.");

                return;
            }
            $features = $decoded['features'];
        } elseif ($type === 'Feature') {
            $features = [$decoded];
        } else {
            // Direct geometry
            $features = [['type' => 'Feature', 'geometry' => $decoded]];
        }

        $featureCount = count($features);

        if ($featureCount === 0 && ! $this->allowEmpty) {
            $fail("The {$attribute} must contain at least one feature.");

            return;
        }

        if (! is_null($this->minFeatures) && $featureCount < $this->minFeatures) {
            $fail("The {$attribute} must contain at least {$this->minFeatures} feature(s).");

            return;
        }

        if (! is_null($this->maxFeatures) && $featureCount > $this->maxFeatures) {
            $fail("The {$attribute} may not contain more than {$this->maxFeatures} feature(s).");

            return;
        }

        foreach ($features as $index => $feature) {
            $geometry = $feature['geometry'] ?? null;
            if (! is_array($geometry) || ! isset($geometry['type']) || ! isset($geometry['coordinates'])) {
                $fail("Feature #{$index} in {$attribute} has an invalid geometry.");

                return;
            }

            $geomType = strtolower((string) $geometry['type']);
            $mode = strtolower($feature['properties']['mode'] ?? $geomType);

            if (! is_null($this->allowedModes)) {
                $modeMatches = in_array($mode, $this->allowedModes, true) || in_array($geomType, $this->allowedModes, true);
                if (! $modeMatches) {
                    $allowedList = implode(', ', $this->allowedModes);
                    $fail("Feature #{$index} has an unpermitted mode '{$mode}'. Allowed: {$allowedList}.");

                    return;
                }
            }

            if (! $this->validateCoordinates($geometry['coordinates'])) {
                $fail("Feature #{$index} in {$attribute} contains invalid coordinate values (longitude must be -180..180, latitude -90..90).");

                return;
            }
        }
    }

    /**
     * Recursively validate coordinates array.
     */
    protected function validateCoordinates(mixed $coords): bool
    {
        if (! is_array($coords)) {
            return false;
        }

        // Base case: [lng, lat] or [lng, lat, alt]
        if (count($coords) >= 2 && is_numeric($coords[0]) && is_numeric($coords[1])) {
            $lng = (float) $coords[0];
            $lat = (float) $coords[1];

            return $lng >= -180 && $lng <= 180 && $lat >= -90 && $lat <= 90;
        }

        // Nested array (e.g. LineString, Polygon, MultiPolygon)
        foreach ($coords as $item) {
            if (! $this->validateCoordinates($item)) {
                return false;
            }
        }

        return true;
    }
}
