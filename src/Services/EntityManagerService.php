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
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getReference(string $entityName, int $id): ?object
    {
        return $this->entityManager->getReference($entityName, $id);
    }

    public function persistAndFlush($entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);
    }

    public function remove($entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    public function create($entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function flush($entity = null): void
    {
        $this->entityManager->flush($entity);
    }

    public function getRepository($entityName)
    {
        return $this->entityManager->getRepository($entityName);
    }
}