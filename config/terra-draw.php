<?php

// config for DevRajThapa/LaravelTerraDraw
return [
    /*
     * Default draw modes enabled.
     * Options: 'polygon', 'rectangle', 'circle', 'linestring', 'freehand', 'point', 'select'
     */
    'modes' => [
        'polygon',
        'rectangle',
        'circle',
        'linestring',
        'freehand',
        'point',
        'select',
    ],

    /*
     * Default initial draw mode activated on load.
     */
    'initial_mode' => 'polygon',

    /*
     * Default map style URL (MapLibre style JSON).
     */
    'map_style' => 'https://demotiles.maplibre.org/style.json',

    /*
     * Default center coordinates [longitude, latitude].
     */
    'center' => [0, 0],

    /*
     * Default zoom level.
     */
    'zoom' => 2,

    /*
     * Default map container height.
     */
    'height' => '450px',

    /*
     * Show toolbar controls by default.
     */
    'toolbar' => true,

    /*
     * Default styling for drawn features (stroke color, fill, etc.)
     */
    'style' => [
        'stroke_color' => '#3388ff',
        'fill_color' => '#3388ff',
        'fill_opacity' => 0.3,
    ],
];
