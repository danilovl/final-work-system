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
use App\Infrastructure\Service\PaginatorService;
use App\Domain\Task\DTO\Api\Output\TaskListOwnerOutput;
use App\Domain\Task\DTO\Api\TaskDTO;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\Facade\TaskFacade;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

readonly class TaskListOwnerHandle
{
    public function __construct(
        private UserService $userService,
        private TaskFacade $taskFacade,
        private PaginatorService $paginatorService
    ) {}

    public function __invoke(Request $request): TaskListOwnerOutput
    {
        $user = $this->userService->getUser();
        $tasksQuery = $this->taskFacade
            ->queryByOwner($user);

        $tasksQuery->setHydrationMode(Task::class);

        $pagination = $this->paginatorService->createPaginationRequest($request, $tasksQuery);

        $result = [];

        /** @var Task $task */
        foreach ($pagination->getItems() as $task) {
            $taskDTO = SerializerHelper::convertToObject($task, TaskDTO::class);
            $result[] = $taskDTO;
        }

        return new TaskListOwnerOutput(
            $pagination->getItemNumberPerPage(),
            $pagination->getTotalItemCount(),
            $pagination->count(),
            $result
        );
    }
}
