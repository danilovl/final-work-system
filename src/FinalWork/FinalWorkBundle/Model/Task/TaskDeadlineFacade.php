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

use Doctrine\ORM\EntityManager;
use FinalWork\FinalWorkBundle\Entity\Task;
use FinalWork\FinalWorkBundle\Entity\Repository\TaskRepository;
use FinalWork\FinalWorkBundle\Services\ParametersService;
use FinalWork\SonataUserBundle\Entity\User;

class TaskDeadlineFacade
{
    /**
     * @var TaskRepository
     */
    private $taskRepository;

    /**
     * @var ParametersService
     */
    private $parametersService;

    /**
     * TaskDeadlineFacade constructor.
     * @param EntityManager $entityManager
     * @param ParametersService $parametersService
     */
    public function __construct(
        EntityManager $entityManager,
        ParametersService $parametersService
    ) {
        $this->taskRepository = $entityManager->getRepository(Task::class);
        $this->parametersService = $parametersService;
    }

    /**
     * @param User $user
     * @param int $limit
     * @return array
     */
    public function getDeadlinesByOwner(
        User $user,
        int $limit = null
    ): array {
        $limit = $limit ?? $this->parametersService->getParam('pagination.task.deadline_limit');

        $taskDeadLinesQuery = $this->taskRepository
            ->getTaskDeadlineByOwner($user)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        $taskDeadLines = [];
        foreach ($taskDeadLinesQuery as $taskDeadLine) {
            $taskDeadLines[] = $taskDeadLine['deadline'];
        }

        return $taskDeadLines;
    }
}
