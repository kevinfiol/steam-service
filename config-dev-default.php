<?php declare(strict_types = 1);

return [
    'app' => [
        'templates_path' => __DIR__ . '/src/Templates/',
        'hero_path'      => __DIR__ . '/src/data/heroes.json',
        'steam_api'      => 'YOUR_STEAM_API_KEY_GOES_HERE'
    ],
    'doctrine' => [
        'namespace'  => 'Entities',
        'entities'   => [__DIR__ . '/src/entities'],
        'dev_mode'   => true,
        'cache'      => __DIR__ . '/.cache',
        'connection' => [
            'driver' => 'pdo_sqlite',
            'path'   => __DIR__ . '/steam.db'
        ]
    ]
];