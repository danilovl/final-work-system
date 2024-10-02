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

use App\Application\Constant\TabTypeConstant;
use App\Application\Helper\SerializerHelper;
use App\Domain\Task\DTO\Api\Output\TaskListWorkOutput;
use App\Domain\Task\DTO\Api\TaskDTO;
use App\Domain\Task\Entity\Task;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Service\WorkDetailTabService;
use Symfony\Component\HttpFoundation\Request;

readonly class TaskListWorkHandle
{
    public function __construct(
        private UserService $userService,
        private WorkDetailTabService $workDetailTabService
    ) {}

    public function __invoke(Request $request, Work $task): TaskListWorkOutput
    {
        $user = $this->userService->getUser();
        $pagination = $this->workDetailTabService->getTabPagination(
            $request,
            TabTypeConstant::TAB_TASK->value,
            $task,
            $user,
            true
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
