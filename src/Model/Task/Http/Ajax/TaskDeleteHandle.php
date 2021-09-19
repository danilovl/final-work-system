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

namespace App\Model\Task\Http\Ajax;

use App\Entity\Task;
use App\Constant\AjaxJsonTypeConstant;
use App\Service\{
    RequestService,
    EntityManagerService
};
use Symfony\Component\HttpFoundation\JsonResponse;

class TaskDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private EntityManagerService $entityManagerService
    ) {
    }

    public function handle(Task $task): JsonResponse
    {
        $this->entityManagerService->remove($task);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}