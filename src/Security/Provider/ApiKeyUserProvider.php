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

namespace App\Security\Provider;

use App\Entity\ApiUser;
use App\Model\ApiUser\ApiUserFacade;
use Symfony\Component\Security\Core\Exception\{
    UnsupportedUserException,
    UsernameNotFoundException
};
use Symfony\Component\Security\Core\User\{
    UserInterface,
    UserProviderInterface
};

class ApiKeyUserProvider implements UserProviderInterface
{
    private ApiUserFacade $apiUserFacade;

    public function __construct(ApiUserFacade $apiUserFacade)
    {
        $this->apiUserFacade = $apiUserFacade;
    }

    public function loadUserByUsername($username): UserInterface
    {
        /** @var ApiUser|null $user */
        $user = $this->apiUserFacade->findByApiKey($username);
        if ($user === null) {
            throw new UsernameNotFoundException();
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }

    public function supportsClass($class): bool
    {
        return ApiUser::class === $class;
    }
}