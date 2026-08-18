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
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
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
    TaskListSolverHandle,
    TaskChangeStatusHandle,
    TaskNotifyCompleteHandle,
    TaskDeleteHandle
};
use App\Domain\Work\Entity\Work;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Task')]
readonly class TaskController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private TaskListOwnerHandle $taskListHandle,
        private TaskListSolverHandle $taskListSolverHandle,
        private TaskDetailApiPlatformHandle $taskDetailApiPlatformHandle,
        private TaskDetailHandle $taskDetailHandle,
        private TaskListWorkHandle $taskListWorkHandle,
        private TaskChangeStatusHandle $taskChangeStatusHandle,
        private TaskNotifyCompleteHandle $taskNotifyCompleteHandle,
        private TaskDeleteHandle $taskDeleteHandle
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

    #[OA\Get(
        path: '/api/key/tasks/{id_task}/works/{id_work}',
        description: 'Retrieves detailed information about a task by task and work IDs.',
        summary: 'Task detail'
    )]
    #[OA\Parameter(
        name: 'id_task',
        description: 'Task ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 123)
    )]
    #[OA\Parameter(
        name: 'id_work',
        description: 'Work ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 456)
    )]
    #[OA\Response(
        response: 200,
        description: 'Task detail',
        content: new OA\JsonContent(ref: new Model(type: TaskDetailOutput::class))
    )]
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

    #[HashidsRequestConverterAttribute(requestAttributesKeys: ['id_work', 'id_task'])]
    #[EntityRelationValidatorAttribute(sourceEntity: Task::class, targetEntity: Work::class)]
    public function changeStatus(
        string $type,
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_task' => 'id'])] Task $task
    ): JsonResponse {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $task);

        return $this->taskChangeStatusHandle->__invoke($type, $task);
    }

    #[HashidsRequestConverterAttribute(requestAttributesKeys: ['id_work', 'id_task'])]
    #[EntityRelationValidatorAttribute(sourceEntity: Task::class, targetEntity: Work::class)]
    public function notifyComplete(
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_task' => 'id'])] Task $task
    ): JsonResponse {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::TASK_NOTIFY_COMPLETE->value, $task);

        return $this->taskNotifyCompleteHandle->__invoke($task);
    }

    #[HashidsRequestConverterAttribute(requestAttributesKeys: ['id_work', 'id_task'])]
    #[EntityRelationValidatorAttribute(sourceEntity: Task::class, targetEntity: Work::class)]
    public function delete(
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_task' => 'id'])] Task $task
    ): JsonResponse {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::DELETE->value, $task);

        return $this->taskDeleteHandle->__invoke($task);
    }
}
