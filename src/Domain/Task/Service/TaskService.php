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
use Doctrine\Common\Collections\{
    Criteria,
    Collection
};
use App\Domain\Work\Entity\Work;

class TaskService
{
    /**
     * @return Collection<Task>
     */
    public function getActiveWorkTask(Work $work): Collection
    {
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
        $completeTasks = 0;

        foreach ($tasks as $task) {
            if ($task->isComplete()) {
                $completeTasks++;
            }
        }

        return round(($completeTasks / $taskCount) * 100);
    }
}
