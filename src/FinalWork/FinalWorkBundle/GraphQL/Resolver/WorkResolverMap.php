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

namespace FinalWork\FinalWorkBundle\GraphQL\Resolver;

use ArrayObject;
use FinalWork\FinalWorkBundle\Model\Work\WorkFacade;
use FinalWork\FinalWorkBundle\Model\WorkStatus\WorkStatusFacade;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class WorkResolverMap extends ResolverMap
{
    /**
     * @var WorkFacade
     */
    private $workFacade;

    /**
     * @var WorkStatusFacade
     */
    private $workStatusFacade;

    /**
     * WorkResolverMap constructor.
     * @param WorkFacade $workFacade
     * @param WorkStatusFacade $workStatusFacade
     */
    public function __construct(
        WorkFacade $workFacade,
        WorkStatusFacade $workStatusFacade
    ) {
        $this->workFacade = $workFacade;
        $this->workStatusFacade = $workStatusFacade;
    }

    /**
     * @return array|array[]>
     */
    protected function map()
    {
        return [
            'doctrine' => [
                'work' => function ($value, ArgumentInterface $args) {
                    $id = (int)$args['id'];

                    return $this->workFacade->find($id);
                },
                'workStatus' => function ($value, ArgumentInterface $args, ArrayObject $context, ResolveInfo $info) {
                    $id = (int)$args['id'];

                    return $this->workStatusFacade->find($id);
                },
                'workStatusList' => function ($value, ArgumentInterface $args) {
                    $limit = $args['limit'] ?? null;

                    return $this->workStatusFacade->findAll($limit);
                },
            ]
        ];
    }
}