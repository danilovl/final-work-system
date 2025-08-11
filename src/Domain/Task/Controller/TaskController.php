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

namespace App\Domain\Task\Controller;

use App\Application\Constant\VoterSupportConstant;
use App\Application\Service\AuthorizationCheckerService;
use Danilovl\HashidsBundle\Attribute\HashidsRequestConverterAttribute;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use App\Domain\Task\Http\{
    TaskListHandle,
    TaskEditHandle,
    TaskCreateHandle,
    TaskCreateSeveralHandle
};
use App\Domain\Task\Entity\Task;
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class TaskController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private TaskListHandle $taskListHandle,
        private TaskCreateHandle $taskCreateHandle,
        private TaskCreateSeveralHandle $taskCreateSeveralHandle,
        private TaskEditHandle $taskEditHandle
    ) {}

    public function list(Request $request): Response
    {
        return $this->taskListHandle->__invoke($request);
    }

    public function create(Request $request, Work $work): Response
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $work);

        return $this->taskCreateHandle->__invoke($request, $work);
    }

    public function createSeveral(Request $request): Response
    {
        return $this->taskCreateSeveralHandle->__invoke($request);
    }

    #[HashidsRequestConverterAttribute(requestAttributesKeys: ['id_work', 'id_task'])]
    public function edit(
        Request $request,
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_task' => 'id'])] Task $task
    ): Response {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $task);

        return $this->taskEditHandle->__invoke($request, $work, $task);
    }
}
