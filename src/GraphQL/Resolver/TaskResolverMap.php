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

use App\Model\Task\TaskFacade;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class TaskResolverMap extends ResolverMap
{
    private TaskFacade $taskService;

    public function __construct(TaskFacade $taskService)
    {
        $this->taskService = $taskService;
    }

    protected function map()
    {
        return [
            'doctrine' => [
                'task' => function ($value, ArgumentInterface $args) {
                    $id = (int)$args['id'];

                    return $this->taskService->find($id);
                },
                'taskList' => function ($value, ArgumentInterface $args) {
                    $limit = $args['limit'] ?? null;

                    return $this->taskService->findAll($limit);
                },
            ]
        ];
    }
}