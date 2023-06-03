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
use App\Domain\Task\Entity\Task;
use Danilovl\HashidsBundle\Attribute\HashidsRequestConverterAttribute;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

class TaskController extends AbstractController
{
    public function __construct(
        private readonly TaskListOwnerHandle $taskListHandle,
        private readonly TaskListSolverHandle $taskListSolverHandle,
        private readonly TaskDetailHandle $taskDetailHandle,
        private readonly TaskListWorkHandle $taskListWorkHandle
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
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $task);

        return $this->taskDetailHandle->handle($task);
    }

    public function listWork(Request $request, Work $work): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $work);

        return $this->taskListWorkHandle->handle($request, $work);
    }
}
