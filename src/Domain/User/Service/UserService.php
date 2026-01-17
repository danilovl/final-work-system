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

namespace App\Domain\User\Service;

use App\Application\Exception\UserNotExistException;
use App\Infrastructure\Service\EntityManagerService;
use App\Domain\User\Entity\User;
use App\Domain\User\Facade\UserFacade;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class UserService
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private EntityManagerService $entityManagerService,
        private UserFacade $userFacade
    ) {}

    public function getUserOrNull(): ?User
    {
        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            return null;
        }

        $user = $token->getUser();
        if (!is_object($user) || !$user instanceof User) {
            return null;
        }

        return $this->refreshUser($user);
    }

    public function getUser(): User
    {
        $user = $this->getUserOrNull();
        if ($user === null) {
            throw new UserNotExistException('User must exist.');
        }

        return $this->refreshUser($user);
    }

    private function refreshUser(User $user): User
    {
        $isInIdentityMap = $this->entityManagerService
            ->getUnitOfWork()
            ->isInIdentityMap($user);

        if ($isInIdentityMap) {
            return $user;
        }

        return $this->userFacade->findNotNull($user->getId());
    }
}
