<?php declare(strict_types = 1);

$db = parse_url(getenv('DATABASE_URL'));

return [
    'app' => [
        'templates_path' => __DIR__ . '/src/Templates/',
        'hero_path'      => __DIR__ . '/src/data/heroes.json',
        'steam_api'      => getenv('STEAM_API')
    ],
    'doctrine' => [
        'namespace'  => 'Entities',
        'entities'   => [__DIR__ . '/src/entities'],
        'dev_mode'   => false,
        'cache'      => __DIR__ . '/.cache',
        'connection' => [
            'driver'   => 'pdo_pgsql',
            'dbname'   => substr($db['path'], 1),
            'user'     => $db['user'],
            'password' => $db['pass'],
            'host'     => $db['host'],
            'port'     => $db['port']
        ]
    ]
];