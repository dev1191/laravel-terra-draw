<?php

// config for DevRajThapa/LaravelTerraDraw
return [
 /*
     * Which draw modes are enabled by default.
     * Options: 'polygon', 'point', 'linestring', 'circle'
     */
    'modes' => ['polygon', 'point', 'linestring','freehand','circle'],

    /*
     * Default map style URL used when a MapLibre style isn't
     * explicitly passed to the component.
     */
    'map_style' => 'https://demotiles.maplibre.org/style.json',

    /*
     * Default styling for drawn features (stroke color, fill, etc.)
     */
    'style' => [
        'stroke_color' => '#3388ff',
        'fill_color' => '#3388ff',
        'fill_opacity' => 0.3,
    ],
];
