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

namespace App\Domain\Security\Provider;

use App\Domain\ApiUser\Entity\ApiUser;
use Override;
use Symfony\Component\Security\Core\Exception\{
    UnsupportedUserException,
    UserNotFoundException
};
use Symfony\Component\Security\Core\User\{
    UserInterface,
    UserProviderInterface
};
use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Domain\ApiUser\Bus\Query\ApiUserByKey\{GetApiUserByKeyQuery, GetApiUserByKeyQueryResult};

readonly class ApiKeyUserProvider implements UserProviderInterface
{
    public function __construct(private QueryBusInterface $queryBus) {}

    #[Override]
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->loadUserByUsername($identifier);
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        $getApiUserByKeyQuery = GetApiUserByKeyQuery::create($username);
        /** @var GetApiUserByKeyQueryResult $getApiUserByKeyQueryResult */
        $getApiUserByKeyQueryResult = $this->queryBus->handle($getApiUserByKeyQuery);
        $user = $getApiUserByKeyQueryResult->apiUser;

        return $user ?? throw new UserNotFoundException;
    }

    #[Override]
    public function refreshUser(UserInterface $user): UserInterface
    {
        throw new UnsupportedUserException;
    }

    #[Override]
    public function supportsClass(string $class): bool
    {
        return ApiUser::class === $class;
    }
}
