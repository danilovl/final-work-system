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

namespace App\GraphQL\Resolver;

use App\Model\Task\Facade\TaskFacade;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class TaskResolverMap extends ResolverMap
{
    private TaskFacade $taskService;

    public function __construct(TaskFacade $taskService)
    {
        $this->taskService = $taskService;
    }

    protected function map(): array
    {
        return [
            'doctrine' => [
                'task' => fn($value, ArgumentInterface $args) => $this->taskService->find((int) $args['id']),
                'taskList' => fn($value, ArgumentInterface $args) => $this->taskService->findAll($args['limit'] ?? null),
            ]
        ];
    }
}
