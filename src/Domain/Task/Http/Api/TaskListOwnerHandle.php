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

use Danilovl\ObjectToArrayTransformBundle\Service\ObjectToArrayTransformService;
use App\Application\Service\{
    UserService,
    PaginatorService
};
use App\Domain\Task\Facade\TaskFacade;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class TaskListOwnerHandle
{
    public function __construct(
        private UserService $userService,
        private TaskFacade $taskFacade,
        private PaginatorService $paginatorService,
        private ObjectToArrayTransformService $objectToArrayTransformService
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $user = $this->userService->getUser();
        $tasksQuery = $this->taskFacade
            ->queryTasksByOwner($user);

        $pagination = $this->paginatorService->createPaginationRequest($request, $tasksQuery);

        $tasks = [];
        foreach ($pagination as $task) {
            $tasks[] = $this->objectToArrayTransformService->transform('api_key_field', $task);
        }

        return new JsonResponse([
            'count' => $pagination->count(),
            'totalCount' => $pagination->getTotalItemCount(),
            'tasks' => $tasks
        ]);
    }
}
