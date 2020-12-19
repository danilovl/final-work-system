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

namespace App\Services;

use Doctrine\ORM\EntityManager;

class EntityManagerService
{
    public function __construct(private EntityManager $entityManager)
    {
    }

    public function getReference(string $entityName, int $id): ?object
    {
        return $this->entityManager->getReference($entityName, $id);
    }

    public function persistAndFlush(mixed $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);
    }

    public function remove(mixed $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush($entity);
    }

    public function create(mixed $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);
    }

    public function flush(mixed $entity = null): void
    {
        $this->entityManager->flush($entity);
    }

    public function getRepository(string $entityName)
    {
        return $this->entityManager->getRepository($entityName);
    }

    public function clear(string $entityName = null): void
    {
        $this->entityManager->clear($entityName);
    }
}