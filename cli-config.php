<?php declare(strict_types = 1);

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use App\Config\Config;
use Scripts\EntityManagerFactory;

require_once __DIR__ . '/vendor/autoload.php';

$app_env = getenv('APP_ENV') ?? 'dev';

switch ($app_env) {
    case 'prod':
        $configArray = include_once __DIR__ . '/config-prod.php';
        break;
    case 'dev':
        $configArray = include_once __DIR__ . '/config-dev.php';
        break;
    default:
        $configArray = include_once __DIR__ . '/config-dev.php';
}

$config = new Config($configArray);
$factory = new EntityManagerFactory($config);
$entityManager = $factory->createEntityManager();

return ConsoleRunner::createHelperSet($entityManager);