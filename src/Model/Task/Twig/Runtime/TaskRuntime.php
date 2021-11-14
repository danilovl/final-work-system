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

namespace App\Model\Task\Twig\Runtime;

use App\Model\Task\Service\TaskService;
use Doctrine\Common\Collections\Collection;
use App\Entity\Work;
use Twig\Extension\AbstractExtension;

class TaskRuntime extends AbstractExtension
{
    public function __construct(private TaskService $taskService)
    {
    }

    public function getCompleteTaskPercentage(
        Work $work,
        Collection $tasks = null
    ): float {
        return $this->taskService->getCompleteTaskPercentage($work, $tasks);
    }
}
