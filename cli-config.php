<?php declare(strict_types = 1);

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use App\Config\Config;
use Scripts\EntityManagerFactory;

require_once __DIR__ . '/vendor/autoload.php';

$configArray = include_once __DIR__ . '/config.php';
$config = new Config($configArray);

$factory = new EntityManagerFactory($config);
$entityManager = $factory->createEntityManager();

return ConsoleRunner::createHelperSet($entityManager);