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

namespace FinalWork\FinalWorkBundle\Model\Task;

use Doctrine\ORM\{
    Query,
    EntityManager
};
use FinalWork\FinalWorkBundle\Entity\Repository\TaskRepository;
use FinalWork\FinalWorkBundle\Entity\Task;
use FinalWork\SonataUserBundle\Entity\User;

class TaskFacade
{
    /**
     * @var TaskRepository
     */
    private $taskRepository;

    /**
     * TaskFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->taskRepository = $entityManager->getRepository(Task::class);
    }

    /**
     * @param int $id
     * @return Task|null
     */
    public function find(int $id): ?Task
    {
       return $this->taskRepository->find($id);
    }

    /**
     * @param int|null $limit
     * @return array
     */
    public function findAll(int $limit = null): array
    {
        return $this->taskRepository
            ->baseQueryBuilder()
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @return Query
     */
    public function queryTasksByOwner(User $user): Query
    {
        return $this->taskRepository
            ->findAllByOwner($user)
            ->getQuery();
    }
}
