<?php declare(strict_types = 1);

namespace App;

use Slim\App;
use Slim\Container;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Create Dependencies
 */
$container = new Container();
$config    = include_once __DIR__ . '/Config.php';

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