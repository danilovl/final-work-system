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

use App\Entity\User;
use App\Model\User\UserFacade;
use App\Service\EntityManagerService;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\{
    UserInterface,
    UserProviderInterface
};

class AppUserProvider implements UserProviderInterface
{
    public function __construct(
        private UserFacade $userFacade,
        private EntityManagerService $entityManagerService
    ) {
    }

    public function loadUserByUsername($username): UserInterface
    {
        $user = $this->userFacade->findUserByUsername($username);

        return $user ?? throw new UsernameNotFoundException;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->userFacade->findUserByUsername($user->getUsername());
    }

    public function supportsClass($class): bool
    {
        return User::class === $class;
    }
}