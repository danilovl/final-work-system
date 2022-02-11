<?php declare(strict_types=1);

/*
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
use App\Domain\Task\Http\{
    TaskListHandle,
    TaskEditHandle,
    TaskCreateHandle,
    TaskCreateSeveralHandle
};
use App\Domain\Task\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Domain\Work\Entity\Work;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class TaskController extends AbstractController
{
    public function __construct(
        private TaskListHandle $taskListHandle,
        private TaskCreateHandle $taskCreateHandle,
        private TaskCreateSeveralHandle $taskCreateSeveralHandle,
        private TaskEditHandle $taskEditHandle
    ) {
    }

    public function list(Request $request): Response
    {
        return $this->taskListHandle->handle($request);
    }

    public function create(Request $request, Work $work): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        return $this->taskCreateHandle->handle($request, $work);
    }

    public function createSeveral(Request $request): Response
    {
        return $this->taskCreateSeveralHandle->handle($request);
    }

    #[ParamConverter('work', class: Work::class, options: ['id' => 'id_work'])]
    #[ParamConverter('task', class: Task::class, options: ['id' => 'id_task'])]
    public function edit(
        Request $request,
        Work $work,
        Task $task
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $task);

        return $this->taskEditHandle->handle($request, $work, $task);
    }
}
