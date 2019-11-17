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

namespace FinalWork\FinalWorkBundle\Services;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\{
    ORMException,
    EntityManager,
    EntityRepository,
    OptimisticLockException
};

class EntityManagerService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * EntityManagerService constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $entityName
     * @param int $id
     * @return object|null
     * @throws ORMException
     */
    public function getReference(string $entityName, int $id): ?object
    {
        return $this->entityManager->getReference($entityName, $id);
    }

    /**
     * @param $entity
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function persistAndFlush($entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);
    }

    /**
     * @param $entity
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove($entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    /**
     * @param $entity
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function create($entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /**
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function flush(): void
    {
        $this->entityManager->flush();
    }

    /**
     * @param $entityName
     * @return ObjectRepository|EntityRepository
     */
    public function getRepository($entityName): ObjectRepository
    {
        return $this->entityManager->getRepository($entityName);
    }
}