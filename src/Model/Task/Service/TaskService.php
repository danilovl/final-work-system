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

namespace App\Model\Task\Service;

use Doctrine\Common\Collections\{
    Criteria,
    Collection
};
use App\Entity\Work;

class TaskService
{
    public function getActiveWorkTask(Work $work): Collection
    {
        $allTask = $work->getTasks();
        $criteriaActive = Criteria::create()->where(Criteria::expr()->eq('active', true));

        return $allTask->matching($criteriaActive);
    }

    public function getCompleteTaskPercentage(
        Work $work,
        Collection $tasks = null
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

        return round(($completeTasks / $taskCount) * 100, 0);
    }
}

