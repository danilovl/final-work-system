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

namespace App\Domain\Task\Bus\Query\ActiveTasksPreload;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\Task\Service\TaskService;

readonly class GetActiveTasksPreloadQueryHandler implements QueryHandlerInterface
{
    public function __construct(private TaskService $taskService) {}

    public function __invoke(GetActiveTasksPreloadQuery $query): GetActiveTasksPreloadQueryResult
    {
        $this->taskService->preloadActiveTasks($query->works);

        return new GetActiveTasksPreloadQueryResult(true);
    }
}
