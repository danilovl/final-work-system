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

use Danilovl\ObjectDtoMapper\Service\ObjectToDtoMapperInterface;
use App\Domain\Task\DTO\Api\TaskDTO;
use App\Domain\Task\Entity\Task;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class TaskDetailHandle
{
    public function __construct(private ObjectToDtoMapperInterface $objectToDtoMapper) {}

    public function __invoke(Task $task): JsonResponse
    {
        $ignoreAttributes = ['user:read:author', 'user:read:supervisor', 'user:read:opponent', 'user:read:consultant'];
        /** @var TaskDTO $result */
        $result = $this->objectToDtoMapper->map(
            source: $task,
            target: TaskDTO::class,
            ignoreGroups: $ignoreAttributes
        );

        return new JsonResponse($result);
    }
}
