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

use App\Infrastructure\Service\EntityManagerService;
use App\Domain\User\Entity\User;
use Override;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\{
    UserInterface,
    UserProviderInterface,
    PasswordUpgraderInterface,
    PasswordAuthenticatedUserInterface
};
use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\User\Bus\Query\UserByUsername\{GetUserByUsernameQuery, GetUserByUsernameQueryResult};

class AppUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly EntityManagerService $entityManagerService
    ) {}

    /**
     * @param User $user
     */
    #[Override]
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        $user->setPassword($newHashedPassword);
        $user->setSalt(null);

        $this->entityManagerService->flush();
    }

    #[Override]
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->loadUserByUsername($identifier);
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        $getUserByUsernameQuery = GetUserByUsernameQuery::create($username);
        /** @var GetUserByUsernameQueryResult $getUserByUsernameQueryResult */
        $getUserByUsernameQueryResult = $this->queryBus->handle($getUserByUsernameQuery);
        $user = $getUserByUsernameQueryResult->user;

        return $user ?? throw new UserNotFoundException;
    }

    #[Override]
    public function refreshUser(UserInterface $user): UserInterface
    {
        $userIdentifier = $user->getUserIdentifier();
        $getUserByUsernameQuery = GetUserByUsernameQuery::create($userIdentifier);
        /** @var GetUserByUsernameQueryResult $getUserByUsernameQueryResult */
        $getUserByUsernameQueryResult = $this->queryBus->handle($getUserByUsernameQuery);
        $user = $getUserByUsernameQueryResult->user;

        return $user ?? throw new UserNotFoundException;
    }

    #[Override]
    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }
}
