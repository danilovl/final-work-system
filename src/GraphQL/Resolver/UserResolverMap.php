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

use App\Model\User\UserFacade;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class UserResolverMap extends ResolverMap
{
    private UserFacade $userService;

    public function __construct(UserFacade $userService)
    {
        $this->userService = $userService;
    }

    protected function map()
    {
        return [
            'doctrine' => [
                'user' => function ($value, ArgumentInterface $args) {
                    $id = (int) $args['id'];

                    return $this->userService->find($id);
                }
            ]
        ];
    }
}