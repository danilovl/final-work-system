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
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function getReference(string $entityName, int $id): ?object
    {
        return $this->entityManager->getReference($entityName, $id);
    }

    public function persistAndFlush(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function remove(object $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    public function detach(object $entity): void
    {
        $this->entityManager->detach($entity);
        $this->entityManager->flush();
    }

    public function refresh(object $entity): void
    {
        $this->entityManager->refresh($entity);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    public function getRepository(string $entityName): ObjectRepository
    {
        return $this->entityManager->getRepository($entityName);
    }

    public function clear(): void
    {
        $this->entityManager->clear();
    }

    public function getConnection(): Connection
    {
        return $this->entityManager->getConnection();
    }
}
