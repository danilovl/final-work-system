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

namespace App\Domain\Task\Service;

use App\Domain\Task\Entity\Task;
use App\Domain\Task\Facade\TaskFacade;
use Doctrine\Common\Collections\{
    Criteria,
    Collection,
    ArrayCollection
};
use App\Domain\Work\Entity\Work;

class TaskService
{
    /** @var array<int, Collection<Task>> */
    private array $activeTasksCache = [];

    public function __construct(private readonly TaskFacade $taskFacade) {}

    /**
     * @param Work[] $works
     */
    public function preloadActiveTasks(array $works): void
    {
        if (empty($works)) {
            return;
        }

        $activeTasks = $this->taskFacade->queryByWorks($works, true)->getResult();

        foreach ($works as $work) {
            $this->activeTasksCache[$work->getId()] = new ArrayCollection;
        }

        /** @var Task $task */
        foreach ($activeTasks as $task) {
            $workId = $task->getWork()->getId();
            if (isset($this->activeTasksCache[$workId])) {
                $this->activeTasksCache[$workId]->add($task);
            }
        }
    }

    /**
     * @return Collection<Task>
     */
    public function getActiveWorkTask(Work $work): Collection
    {
        if (isset($this->activeTasksCache[$work->getId()])) {
            return $this->activeTasksCache[$work->getId()];
        }

        $allTask = $work->getTasks();
        $criteriaActive = Criteria::create()->where(Criteria::expr()->eq('active', true));

        return $allTask->matching($criteriaActive);
    }

    /**
     * @param Collection<Task>|null $tasks
     */
    public function getCompleteTaskPercentage(
        Work $work,
        ?Collection $tasks = null
    ): float {
        if ($tasks === null) {
            $tasks = $this->getActiveWorkTask($work);
        }

        $taskCount = $tasks->count();
        if ($taskCount === 0) {
            return 0.0;
        }

        $completeTasks = 0;

        foreach ($tasks as $task) {
            if ($task->isComplete()) {
                $completeTasks++;
            }
        }

        return round(($completeTasks / $taskCount) * 100);
    }
}
