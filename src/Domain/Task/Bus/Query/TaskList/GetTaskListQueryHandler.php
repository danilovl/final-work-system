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

namespace App\Domain\Task\Bus\Query\TaskList;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Infrastructure\Service\PaginatorService;
use App\Domain\Task\Facade\TaskFacade;
use App\Domain\Task\Repository\Elastica\ElasticaTaskRepository;

readonly class GetTaskListQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private TaskFacade $taskFacade,
        private PaginatorService $paginatorService,
        private ElasticaTaskRepository $elasticaTaskRepository
    ) {}

    public function __invoke(GetTaskListQuery $query): GetTaskListQueryResult
    {
        $user = $query->user;
        $tasksQuery = $this->taskFacade->queryTasksByOwner($user);

        if ($query->search) {
            $taskIds = $this->elasticaTaskRepository->getIdsByOwnerAndSearch($user, $query->search);
            $tasksQuery = $this->taskFacade->queryByIds($taskIds);
        }

        $isTasksInComplete = $this->taskFacade->isTasksCompleteByOwner($user, false);
        $tasks = $this->paginatorService->createPaginationRequest(
            $query->request,
            $tasksQuery,
            detachEntity: true
        );

        return new GetTaskListQueryResult(
            tasks: $tasks,
            isTasksInComplete: $isTasksInComplete
        );
    }
}
