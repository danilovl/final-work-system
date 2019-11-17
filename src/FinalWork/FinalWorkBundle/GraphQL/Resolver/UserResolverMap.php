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

use FinalWork\FinalWorkBundle\Model\User\UserFacade;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class UserResolverMap extends ResolverMap
{
    /**
     * @var UserFacade
     */
    private $userService;

    /**
     * UserResolverMap constructor.
     * @param UserFacade $userService
     */
    public function __construct(UserFacade $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @return array|array[]>
     */
    protected function map()
    {
        return [
            'doctrine' => [
                'user' => function ($value, ArgumentInterface $args) {
                    $id = (int)$args['id'];

                    return $this->userService->find($id);
                },
            ]
        ];
    }
}