<?php

use Illuminate\Support\Facades\Blade;

it('can render the terra-draw blade component with default props', function () {
    $rendered = (string) $this->blade(
        '<x-laravel-terra-draw::terra-draw id="test-map" name="boundary" />'
    );

    expect($rendered)
        ->toContain('id="test-map"')
        ->toContain('data-terra-draw')
        ->toContain('data-name="boundary"')
        ->toContain('name="boundary"')
        ->toContain('data-terra-draw-input="test-map"')
        ->toContain('class="terra-draw-toolbar"');
});

it('can render the terra-draw component with custom modes and height', function () {
    $rendered = (string) $this->blade(
        '<x-laravel-terra-draw::terra-draw id="custom-map" name="geo" :modes="[\'polygon\', \'rectangle\']" height="600px" />'
    );

    expect($rendered)
        ->toContain('id="custom-map"')
        ->toContain('style="width: 100%; height: 600px;')
        ->toContain('data-mode="polygon"')
        ->toContain('data-mode="rectangle"')
        ->not->toContain('data-mode="circle"');
});

it('can disable the toolbar when toolbar prop is false', function () {
    $rendered = (string) $this->blade(
        '<x-laravel-terra-draw::terra-draw id="no-toolbar-map" name="geo" :toolbar="false" />'
    );

    expect($rendered)
        ->toContain('id="no-toolbar-map"')
        ->not->toContain('class="terra-draw-toolbar"');
});

it('renders blade directives for styles and scripts', function () {
    $styles = Blade::render('@terraDrawStyles');
    $scripts = Blade::render('@terraDrawScripts');

    expect($styles)->toContain('maplibre-gl.css');
    expect($scripts)->toContain('terra-draw');
});
