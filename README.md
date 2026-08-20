# Laravel Terra Draw

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dev1191/laravel-terra-draw.svg?style=flat-square)](https://packagist.org/packages/dev1191/laravel-terra-draw)
[![GitHub Tests Action Status](https://github.com/dev1191/laravel-terra-draw/actions/workflows/run-tests.yml/badge.svg)](https://github.com/dev1191/laravel-terra-draw/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://github.com/dev1191/laravel-terra-draw/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/dev1191/laravel-terra-draw/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/dev1191/laravel-terra-draw.svg?style=flat-square)](https://packagist.org/packages/dev1191/laravel-terra-draw)

Seamless geospatial drawing and GeoJSON form integration for Laravel powered by **[Terra Draw](https://github.com/JamesLMilner/terra-draw)** and **[MapLibre GL](https://maplibre.org/)**.

Easily embed interactive vector maps into Blade templates, forms, and admin panels with automatic GeoJSON data synchronization.

---

## Features

- 🗺️ **Easy Blade Component**: Use `<x-laravel-terra-draw::terra-draw />` anywhere in your views.
- ✍️ **Comprehensive Drawing Modes**: Support for Polygon, Rectangle, Circle, LineString, Freehand, Point, and Select / Edit.
- 🔄 **Automatic Form Sync**: Syncs drawn GeoJSON features directly into a hidden input for seamless Laravel form submission and Livewire binding.
- 🎨 **Built-in Toolbar**: Sleek, customizable drawing controls with active mode indicators and clear canvas buttons.
- ⚡ **Zero-Config Directives**: Load assets in seconds using `@terraDrawStyles` and `@terraDrawScripts`.
- ⚙️ **Fully Configurable**: Customizable initial coordinates, zoom levels, MapLibre tile styles, and mode permissions.

---

## Installation

Install the package via Composer:

```bash
composer require dev1191/laravel-terra-draw
```

Publish the configuration file (optional):

```bash
php artisan vendor:publish --tag="laravel-terra-draw-config"
```

Publish the Blade views (optional):

```bash
php artisan vendor:publish --tag="laravel-terra-draw-views"
```

---

## Quick Start

Add the component to your Blade view:

```blade
<!DOCTYPE html>
<html>
<head>
    <title>My Map Form</title>
    {{-- Include MapLibre & Toolbar Styles --}}
    @terraDrawStyles
</head>
<body>
    <form method="POST" action="{{ route('locations.store') }}">
        @csrf

        {{-- Terra Draw Component --}}
        <x-laravel-terra-draw::terra-draw 
            name="boundary" 
            :center="[85.3240, 27.7172]" 
            :zoom="12" 
            height="500px" 
        />

        <button type="submit">Save Boundary</button>
    </form>

    {{-- Include Scripts --}}
    @terraDrawScripts
</body>
</html>
```

### Accessing Drawn Data in Controller

When the form is submitted, the GeoJSON payload is available under your input's `name`:

```php
public function store(Request $request)
{
    $request->validate([
        'boundary' => ['required', 'string'],
    ]);

    $geoJson = json_decode($request->input('boundary'), true);

    // Array of drawn features
    $features = $geoJson['features'] ?? [];

    // Save to database / spatial column...
}
```

---

## Component Props Reference

| Prop | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `name` | `string` | `'geometry'` | Name of the hidden input field submitted with the form. |
| `id` | `string` | Auto-generated | Unique identifier for the map container and input. |
| `value` | `string\|array` | `null` | Initial GeoJSON payload to render on load (e.g. existing model data). |
| `center` | `array` | `[0, 0]` | Center coordinates `[longitude, latitude]`. |
| `zoom` | `int\|float` | `2` | Initial map zoom level. |
| `height` | `string` | `'450px'` | Height of the map canvas (e.g. `'500px'`, `'70vh'`). |
| `modes` | `array` | `['polygon', ...]` | Array of enabled drawing modes. |
| `initialMode` | `string` | `'polygon'` | Mode activated immediately when the map loads. |
| `mapStyle` | `string` | Demo Tiles | URL to MapLibre style JSON. |
| `toolbar` | `bool` | `true` | Show or hide the top drawing toolbar. |
| `editable` | `bool` | `true` | When `false`, renders in read-only / static mode. |

---

## Pre-loading Existing GeoJSON

Pass existing GeoJSON data (FeatureCollection, Feature, or JSON string) into the `:value` prop:

```blade
<x-laravel-terra-draw::terra-draw 
    name="area" 
    :value="$polygonModel->geojson" 
    :center="[85.3240, 27.7172]" 
    :zoom="13" 
/>
```

---

## JavaScript Events & Interoperability

The component dispatches DOM events for vanilla JavaScript, Alpine.js, and Livewire:

```javascript
const mapElement = document.getElementById('my-map-id');

mapElement.addEventListener('terra-draw:change', (event) => {
    const { snapshot, type, ids } = event.detail;
    console.log('GeoJSON updated:', snapshot);
});

mapElement.addEventListener('terra-draw:ready', (event) => {
    const { map, draw } = event.detail;
    console.log('MapLibre and TerraDraw ready!', map, draw);
});
```

---

## Configuration (`config/terra-draw.php`)

```php
return [
    'modes' => [
        'polygon',
        'rectangle',
        'circle',
        'linestring',
        'freehand',
        'point',
        'select',
    ],

    'initial_mode' => 'polygon',
    'map_style' => 'https://demotiles.maplibre.org/style.json',
    'center' => [0, 0],
    'zoom' => 2,
    'height' => '450px',
    'toolbar' => true,
];
```

---

## Testing

Run the test suite using Pest:

```bash
composer test
```

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [DevRajThapa](https://github.com/dev1191)
- [James Milner](https://github.com/JamesLMilner) (Creator of Terra Draw)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
