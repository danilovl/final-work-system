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

namespace App\Domain\Task\Controller\Api;

use App\Application\Constant\VoterSupportConstant;
use App\Application\Service\AuthorizationCheckerService;
use App\Domain\Task\Entity\Task;
use Danilovl\HashidsBundle\Attribute\HashidsRequestConverterAttribute;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use App\Domain\Task\Http\Api\{
    TaskDetailHandle,
    TaskListWorkHandle,
    TaskListOwnerHandle,
    TaskListSolverHandle
};
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class TaskController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private TaskListOwnerHandle $taskListHandle,
        private TaskListSolverHandle $taskListSolverHandle,
        private TaskDetailHandle $taskDetailHandle,
        private TaskListWorkHandle $taskListWorkHandle
    ) {}

    public function listOwner(Request $request): JsonResponse
    {
        return $this->taskListHandle->handle($request);
    }

    public function listSolver(Request $request): JsonResponse
    {
        return $this->taskListSolverHandle->handle($request);
    }

    #[HashidsRequestConverterAttribute(requestAttributesKeys: ['id_work', 'id_task'])]
    public function detail(
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_task' => 'id'])] Task $task
    ): JsonResponse {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $task);

        return $this->taskDetailHandle->handle($task);
    }

    public function listWork(Request $request, Work $work): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $work);

        return $this->taskListWorkHandle->handle($request, $work);
    }
}
