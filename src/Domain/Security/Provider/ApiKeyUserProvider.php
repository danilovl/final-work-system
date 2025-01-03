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
use App\Domain\ApiUser\Facade\ApiUserFacade;
use Override;
use Symfony\Component\Security\Core\Exception\{
    UnsupportedUserException,
    UserNotFoundException
};
use Symfony\Component\Security\Core\User\{
    UserInterface,
    UserProviderInterface
};

readonly class ApiKeyUserProvider implements UserProviderInterface
{
    public function __construct(private ApiUserFacade $apiUserFacade) {}

    #[Override]
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->loadUserByUsername($identifier);
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        $user = $this->apiUserFacade->findByApiKey($username);

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
