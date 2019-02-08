<?php declare(strict_types = 1);

namespace App;

use Slim\App;
use Slim\Container;

error_reporting(E_ALL);
file_put_contents("php://stderr", "hello, this is a test!\n");

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Resolve Configuration Variables
 */
$configPath = __DIR__ . '/Config.php';

if (file_exists($configPath) && is_readable($configPath)) {
    $config = include_once __DIR__ . '/Config.php';
} else {
    $config = [
        'STEAM_API' => getenv('STEAM_API'),
    ];
}

/**
 * Create Dependencies
 */
$container          = new Container();
$createDependencies = include_once __DIR__ . '/Dependencies.php';
$dependencies       = $createDependencies($config);

foreach ($dependencies as $name => $factory) {
    $container[$name] = $factory;
}

/**
 * Create Application and Map Routes
 */
$app    = new App($container);
$routes = include_once __DIR__ . '/Routes.php';

foreach ($routes as $path => [$http, $handler]) {
    $app->map($http, $path, $handler);
}

/**
 * Include Middleware
 */
use App\Middleware\CORS;
$app->add( new CORS() );

/**
 * Run Application
 */
$app->run();