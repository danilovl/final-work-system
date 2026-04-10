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

namespace App\Domain\Task\GraphQL\Resolver;

use App\Domain\Task\Entity\Task;
use App\Domain\Task\Facade\TaskFacade;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class TaskResolverMap extends ResolverMap
{
    public function __construct(private readonly TaskFacade $taskService) {}

    protected function map(): array
    {
        return [
            'doctrine' => [
                'task' => function ($value, ArgumentInterface $args): ?Task {
                    /** @var int $id */
                    $id = $args['id'];

                    return $this->taskService->findById($id);
                },
                'taskList' => function ($value, ArgumentInterface $args): array {
                    /** @var int|null $limit */
                    $limit = $args['limit'] ?? null;

                    return $this->taskService->list($limit);
                },
            ]
        ];
    }
}
