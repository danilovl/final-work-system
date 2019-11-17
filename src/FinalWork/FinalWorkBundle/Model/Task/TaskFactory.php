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

use FinalWork\FinalWorkBundle\Entity\Task;
use Doctrine\ORM\{
    ORMException,
    EntityManager,
    OptimisticLockException
};
use Symfony\Component\HttpFoundation\Request;

class TaskFactory
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * TaskFactory constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param TaskModel $taskModel
     * @param Task|null $task
     * @return Task
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function flushFromModel(
        TaskModel $taskModel,
        Task $task = null
    ): Task {
        $task = $task ?? new Task;
        $task = $this->fromModel($task, $taskModel);

        $this->em->persist($task);
        $this->em->flush();

        return $task;
    }

    /**
     * @param Task $task
     * @param TaskModel $taskModel
     * @return Task
     */
    public function fromModel(
        Task $task,
        TaskModel $taskModel
    ): Task {
        $task->setName($taskModel->name);
        $task->setDescription($taskModel->description);
        $task->setComplete($taskModel->complete);
        $task->setActive($taskModel->active);
        $task->setDeadline($taskModel->deadline);
        $task->setWork($taskModel->work);
        $task->setOwner($taskModel->owner);

        return $task;
    }
}
