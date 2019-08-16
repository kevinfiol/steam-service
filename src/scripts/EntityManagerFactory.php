<?php declare(strict_types = 1);

namespace Scripts;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

use App\Config\Config;

class EntityManagerFactory
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function createEntityManager(): EntityManager
    {
        $doctrineConfig = $this->config->get('doctrine');
        $connection   = $doctrineConfig['connection'];
        $entitiesPath = $doctrineConfig['entities'];
        $devMode      = $doctrineConfig['dev_mode'];
        $cachePath    = $doctrineConfig['cache'];

        $metaConfig = Setup::createAnnotationMetadataConfiguration($entitiesPath, $devMode);
        $metaConfig->setMetadataDriverImpl(
            new AnnotationDriver(
                new AnnotationReader(),
                $entitiesPath
            )
        );

        $metaConfig->setMetadataCacheImpl(
            new FilesystemCache($cachePath)
        );

        return EntityManager::create($connection, $metaConfig);
    }
}