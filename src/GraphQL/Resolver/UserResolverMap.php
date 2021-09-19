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

use App\Model\User\Facade\UserFacade;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class UserResolverMap extends ResolverMap
{
    public function __construct(private UserFacade $userService)
    {
    }

    protected function map(): array
    {
        return [
            'doctrine' => [
                'user' => fn($value, ArgumentInterface $args) => $this->userService->find((int) $args['id'])
            ]
        ];
    }
}
