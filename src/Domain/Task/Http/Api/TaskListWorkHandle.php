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

namespace App\Domain\Task\Http\Api;

use App\Application\Helper\SerializerHelper;
use App\Domain\Task\DTO\Api\Output\TaskListWorkOutput;
use App\Domain\Task\DTO\Api\TaskDTO;
use App\Domain\Task\Entity\Task;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\Request;
use App\Domain\Task\Facade\TaskFacade;
use App\Domain\Work\Helper\WorkRoleHelper;
use App\Infrastructure\Service\PaginatorService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Knp\Component\Pager\PaginatorInterface;

readonly class TaskListWorkHandle
{
    public function __construct(
        private UserService $userService,
        private TaskFacade $taskFacade,
        private PaginatorService $paginatorService,
        private ParameterServiceInterface $parameterService
    ) {}

    public function __invoke(Request $request, Work $task): TaskListWorkOutput
    {
        $user = $this->userService->getUser();
        $isSupervisor = WorkRoleHelper::isSupervisor($task, $user);
        $query = $this->taskFacade->queryByWork($task, !$isSupervisor);
        $query->setHydrationMode(Task::class);

        $page = $this->parameterService->getInt('pagination.work_detail_tab.task.page');
        $limit = $this->parameterService->getInt('pagination.work_detail_tab.task.limit');

        $pagination = $this->paginatorService->createPaginationRequest(
            request: $request,
            target: $query,
            page: $page,
            limit: $limit,
            options: [PaginatorInterface::DISTINCT => true]
        );

        $result = [];

        /** @var Task $task */
        foreach ($pagination->getItems() as $task) {
            $taskDTO = SerializerHelper::convertToObject($task, TaskDTO::class);
            $result[] = $taskDTO;
        }

        return new TaskListWorkOutput(
            $pagination->getItemNumberPerPage(),
            $pagination->getTotalItemCount(),
            $pagination->count(),
            $result
        );
    }
}
