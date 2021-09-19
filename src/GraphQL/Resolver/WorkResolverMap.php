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

use App\Model\Work\Facade\WorkFacade;
use App\Model\WorkStatus\Facade\WorkStatusFacade;
use ArrayObject;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class WorkResolverMap extends ResolverMap
{
    public function __construct(
        private WorkFacade $workFacade,
        private WorkStatusFacade $workStatusFacade
    ) {
    }

    protected function map(): array
    {
        return [
            'doctrine' => [
                'work' => fn($value, ArgumentInterface $args) => $this->workFacade->find((int) $args['id']),
                'workStatus' => fn($value, ArgumentInterface $args, ArrayObject $context, ResolveInfo $info) => $this->workStatusFacade->find((int) $args['id']),
                'workStatusList' => fn($value, ArgumentInterface $args) => $this->workStatusFacade->findAll($args['limit'] ?? null)
            ]
        ];
    }
}
