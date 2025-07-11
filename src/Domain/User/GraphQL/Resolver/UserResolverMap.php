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

namespace App\Domain\User\GraphQL\Resolver;

use App\Domain\User\Entity\User;
use App\Domain\User\Facade\UserFacade;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class UserResolverMap extends ResolverMap
{
    public function __construct(private readonly UserFacade $userService) {}

    protected function map(): array
    {
        return [
            'doctrine' => [
                'user' => function ($value, ArgumentInterface $args): ?User {
                    /** @var int $id */
                    $id = $args['id'];

                    return $this->userService->findById($id);
                }
            ]
        ];
    }
}
