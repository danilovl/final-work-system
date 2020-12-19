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
    public function __construct(private ApiUserFacade $apiUserFacade)
    {
    }

    public function loadUserByUsername($username): UserInterface
    {
        $user = $this->apiUserFacade->findByApiKey($username);

        return $user ?? throw new UsernameNotFoundException;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        throw new UnsupportedUserException();
    }

    public function supportsClass($class): bool
    {
        return ApiUser::class === $class;
    }
}