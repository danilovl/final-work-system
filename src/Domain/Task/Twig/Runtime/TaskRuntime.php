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

namespace App\Domain\Task\Twig\Runtime;

use App\Domain\Task\Service\TaskService;
use Danilovl\RenderServiceTwigExtensionBundle\Attribute\AsTwigFunction;
use Doctrine\Common\Collections\Collection;
use App\Domain\Work\Entity\Work;

class TaskRuntime
{
    public function __construct(private readonly TaskService $taskService) {}

    #[AsTwigFunction('task_work_complete_percentage')]
    public function getCompleteTaskPercentage(
        Work $work,
        Collection $tasks = null
    ): float {
        return $this->taskService->getCompleteTaskPercentage($work, $tasks);
    }
}
