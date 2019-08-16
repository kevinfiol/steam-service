<?php declare(strict_types = 1);

namespace App\Database;

use App\Config\Config;
use Doctrine\ORM\EntityManager;

class Database
{
    private $em;
    private $namespace;

    public function __construct(EntityManager $em, Config $config)
    {
        $this->em = $em;
        $doctrineConfig  = $config->get('doctrine');
        $this->namespace = $doctrineConfig['namespace'];
    }

    public function getRows(string $entityName, array $criteria = []): array
    {
        $entityClass = "{$this->namespace}\\{$entityName}";
        $rows = $this->em->getRepository($entityClass)->findBy($criteria);
        return $rows;
    }

    public function addRow(string $entityName, array $values)
    {
        $entityClass = "{$this->namespace}\\{$entityName}";
        $entity      = new $entityClass();

        $entity->setValues($values);
        $this->em->persist($entity);
        $this->em->flush();
    }
}