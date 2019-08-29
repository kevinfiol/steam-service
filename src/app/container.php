<?php declare(strict_types = 1);

use function DI\create;
use function DI\factory;

require __DIR__ . '/../../vendor/autoload.php';

/**
 * Configurations
 */
$app_env = getenv('APP_ENV') ?? 'dev';

switch ($app_env) {
    case 'prod':
        $config = include_once __DIR__ . '/../../config-prod.php';
        break;
    case 'dev':
        $config = include_once __DIR__ . '/../../config-dev.php';
        break;
    default:
        $config = include_once __DIR__ . '/../../config-dev.php';
}

/**
 * PHP Definitions
 */
$builder = new DI\ContainerBuilder();
$builder->addDefinitions([
    // Config Object
    App\Config\Config::class => create()
        ->constructor($config)
    ,

    // Templating Engine
    League\Plates\Engine::class => create()
        ->constructor($config['app']['templates_path'])
    ,

    // Doctrine Entity Manager
    Doctrine\ORM\EntityManager::class => factory([
        Scripts\EntityManagerFactory::class,
        'createEntityManager'
    ])
]);

$container = $builder->build();
return $container;