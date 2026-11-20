<?php 


return [
    'timeout_in' => 30,
    'timeout_out' => 30,
    'cache_minutes' => (int) env('CACHE_DISPLAY_MINUTES', 2),
    'style' => [
        'background' => '#ffffff',
        'primary' => '#04427b',
        'text_primary' => '#ffffff',
        'secondary' => '#f1ff00',
        'text_secondary' => '#000000',
        'header' => '#94ceff',
        'footer' => '#94ceff',
    ],
];