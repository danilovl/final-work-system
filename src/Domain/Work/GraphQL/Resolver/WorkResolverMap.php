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

namespace App\Domain\Work\GraphQL\Resolver;

use App\Domain\Work\Entity\Work;
use App\Domain\Work\Facade\WorkFacade;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Domain\WorkStatus\Facade\WorkStatusFacade;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;

class WorkResolverMap extends ResolverMap
{
    public function __construct(
        private readonly WorkFacade $workFacade,
        private readonly WorkStatusFacade $workStatusFacade
    ) {}

    #[Override]
    protected function map(): array
    {
        return [
            'doctrine' => [
                'work' => function ($value, ArgumentInterface $args): ?Work {
                    /** @var int $id */
                    $id = $args['id'];

                    return $this->workFacade->findById($id);
                },
                'workStatus' => function ($value, ArgumentInterface $args): ?WorkStatus {
                    /** @var int $id */
                    $id = $args['id'];

                    return $this->workStatusFacade->find($id);
                },
                'workStatusList' => function ($value, ArgumentInterface $args): array {
                    /** @var int|null $limit */
                    $limit = $args['limit'] ?? null;

                    return $this->workStatusFacade->findAll($limit);
                }
            ]
        ];
    }
}
