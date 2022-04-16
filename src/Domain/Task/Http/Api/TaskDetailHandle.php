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

use App\Domain\Task\Entity\Task;
use Danilovl\ObjectToArrayTransformBundle\Service\ObjectToArrayTransformService;
use Symfony\Component\HttpFoundation\JsonResponse;

class TaskDetailHandle
{
    public function __construct(private ObjectToArrayTransformService $objectToArrayTransformService)
    {
    }

    public function handle(Task $task): JsonResponse
    {
        return new JsonResponse([
            'task' => $this->objectToArrayTransformService->transform('api_key_field', $task)
        ]);
    }
}
