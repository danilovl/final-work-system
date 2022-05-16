<?php declare(strict_types=1);

/**
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Application\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class EntityManagerService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function getReference(string $entityName, int $id): ?object
    {
        return $this->entityManager->getReference($entityName, $id);
    }

    public function persistAndFlush(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);
    }

    public function remove(object $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush($entity);
    }

    public function create(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);
    }

    public function flush(object $entity = null): void
    {
        $this->entityManager->flush($entity);
    }

    public function getRepository(string $entityName): ObjectRepository
    {
        return $this->entityManager->getRepository($entityName);
    }

    public function clear(string $entityName = null): void
    {
        $this->entityManager->clear($entityName);
    }

    public function refresh(object $object): void
    {
        $this->entityManager->refresh($object);
    }

    public function getConnection(): Connection
    {
        return $this->entityManager->getConnection();
    }
}
