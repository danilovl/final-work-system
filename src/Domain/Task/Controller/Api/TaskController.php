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

use App\Domain\Task\Http\Api\TaskListOwnerHandle;
use App\Domain\Task\Http\Api\TaskListSolverHandle;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

class TaskController
{
    public function __construct(
        private TaskListOwnerHandle $taskListHandle,
        private TaskListSolverHandle $taskListSolverHandle
    ) {
    }

    public function listOwner(Request $request): JsonResponse
    {
        return $this->taskListHandle->handle($request);
    }

    public function listSolver(Request $request): JsonResponse
    {
        return $this->taskListSolverHandle->handle($request);
    }
}
