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

namespace App\Infrastructure\Service;

use App\Application\EventDispatcher\EntityEventDispatcher;
use App\Application\Helper\AttributeHelper;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\{
    EntityManagerInterface,
    UnitOfWork
};
use Doctrine\Persistence\ObjectRepository;

readonly class EntityManagerService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EntityEventDispatcher $entityEventDispatcher
    ) {}

    /**
     * @template T of object
     * @param class-string<T> $entityName
     * @return T|null
     */
    public function getReference(string $entityName, int $id): ?object
    {
        return $this->entityManager->getReference($entityName, $id);
    }

    public function persistAndFlush(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->entityEventDispatcher->onCreate($entity);
    }

    public function remove(object $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $this->entityEventDispatcher->onRemove();
    }

    /**
     * @param class-string $entity
     */
    public function removeNativeSql(string $entity, int $id): void
    {
        $tableName = AttributeHelper::getEntityTableName($entity);
        $sql = sprintf('DELETE FROM %s WHERE id = %d', $tableName, $id);

        $this->entityManager
            ->getConnection()
            ->executeQuery($sql);

        $this->entityEventDispatcher->onRemove();
    }

    public function detach(object $entity): void
    {
        $this->entityManager->detach($entity);
    }

    /**
     * @param object[] $entities
     */
    public function detachArray(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->detach($entity);
        }
    }

    public function refresh(object $entity): void
    {
        $this->entityManager->refresh($entity);
    }

    public function flush(): void
    {
        $this->entityManager->flush();

        $this->entityEventDispatcher->onSave();
    }

    /**
     * @template T of object
     * @param class-string<T> $entityName
     * @return ObjectRepository<T>
     */
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

    public function getUnitOfWork(): UnitOfWork
    {
        return $this->entityManager->getUnitOfWork();
    }
}
