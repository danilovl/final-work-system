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

use App\Application\Attribute\EntityRelationValidatorAttribute;
use App\Application\Constant\VoterSupportConstant;
use App\Infrastructure\Service\AuthorizationCheckerService;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Domain\Task\DTO\Api\Output\{
    TaskDetailOutput,
    TaskListWorkOutput,
    TaskListOwnerOutput,
    TaskListSolverOutput
};
use App\Domain\Task\Entity\Task;
use Danilovl\HashidsBundle\Attribute\HashidsRequestConverterAttribute;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use App\Domain\Task\Http\Api\{
    TaskDetailApiPlatformHandle,
    TaskDetailHandle,
    TaskListWorkHandle,
    TaskListOwnerHandle,
    TaskListSolverHandle
};
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\Request;

readonly class TaskController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private TaskListOwnerHandle $taskListHandle,
        private TaskListSolverHandle $taskListSolverHandle,
        private TaskDetailApiPlatformHandle $taskDetailApiPlatformHandle,
        private TaskDetailHandle $taskDetailHandle,
        private TaskListWorkHandle $taskListWorkHandle
    ) {}

    public function listOwner(Request $request): TaskListOwnerOutput
    {
        return $this->taskListHandle->__invoke($request);
    }

    public function listSolver(Request $request): TaskListSolverOutput
    {
        return $this->taskListSolverHandle->__invoke($request);
    }

    #[HashidsRequestConverterAttribute(requestAttributesKeys: ['id_work', 'id_task'])]
    #[EntityRelationValidatorAttribute(sourceEntity: Task::class, targetEntity: Work::class)]
    public function detailApiPlatform(
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_task' => 'id'])] Task $task
    ): TaskDetailOutput {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $task);

        return $this->taskDetailApiPlatformHandle->__invoke($task);
    }

    #[HashidsRequestConverterAttribute(requestAttributesKeys: ['id_work', 'id_task'])]
    #[EntityRelationValidatorAttribute(sourceEntity: Task::class, targetEntity: Work::class)]
    public function detail(
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_task' => 'id'])] Task $task
    ): JsonResponse {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $task);

        return $this->taskDetailHandle->__invoke($task);
    }

    public function listWork(Request $request, Work $work): TaskListWorkOutput
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $work);

        return $this->taskListWorkHandle->__invoke($request, $work);
    }
}
