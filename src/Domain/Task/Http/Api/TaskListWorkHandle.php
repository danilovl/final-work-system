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
use App\Application\Service\UserService;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Service\WorkDetailTabService;
use Danilovl\ObjectToArrayTransformBundle\Service\ObjectToArrayTransformService;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class TaskListWorkHandle
{
    public function __construct(
        private readonly UserService $userService,
        private readonly WorkDetailTabService $workDetailTabService,
        private readonly ObjectToArrayTransformService $objectToArrayTransformService
    ) {}

    public function handle(Request $request, Work $work): JsonResponse
    {
        $user = $this->userService->getUser();
        $pagination = $this->workDetailTabService->getTabPagination(
            $request,
            TabTypeConstant::TAB_TASK,
            $work,
            $user
        );

        $tasks = [];
        foreach ($pagination as $task) {
            $tasks[] = $this->objectToArrayTransformService->transform('api_key_field', $task);
        }

        return new JsonResponse([
            'count' => $pagination->count(),
            'totalCount' => $pagination->getTotalItemCount(),
            'success' => true,
            'result' => $tasks
        ]);
    }
}
