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

namespace App\Domain\User\Facade;

use App\Application\Exception\{
    RuntimeException,
    UserNotExistException
};
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Doctrine\ORM\{
    Query,
    QueryBuilder
};

readonly class UserFacade
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function findById(int $id): ?User
    {
        /** @var User|null $result */
        $result = $this->userRepository->find($id);

        return $result;
    }

    public function getById(int $id): User
    {
        /** @var User|null $user */
        $user = $this->userRepository->find($id);
        if ($user === null) {
            throw new UserNotExistException("User with id {$id} not found.");
        }

        return $user;
    }

    public function queryBySupervisor(
        User $user,
        string $type,
        iterable|WorkStatus|null $workStatus = null
    ): Query {
        return $this->userRepository
            ->bySupervisor($user, $type, $workStatus)
            ->getQuery();
    }

    public function queryUnusedUsers(User $user): Query
    {
        return $this->userRepository
            ->unusedUser($user)
            ->getQuery();
    }

    public function findByUsername(string $username, ?bool $enable = null): ?User
    {
        /** @var User|null $result */
        $result = $this->userRepository
            ->oneByUsername($username, $enable)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    public function findByEmail(string $email, ?bool $enable = null): ?User
    {
        /** @var User|null $result */
        $result = $this->userRepository
            ->oneByEmail($email, $enable)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    public function findByToken(string $username, string $token, ?bool $enable = null): ?User
    {
        /** @var User|null $result */
        $result = $this->userRepository
            ->oneByToken($username, $token, $enable)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    public function queryAllByUserRoleAndSearch(string $role, ?string $search = null, bool $enable = true): Query
    {
        return $this->userRepository
            ->allByUserRoleAndSearch($role, $search, $enable)
            ->getQuery();
    }

    public function queryBuilderAllByUserRole(string $role): QueryBuilder
    {
        return $this->userRepository->allByUserRole($role);
    }

    public function queryBuilderBySearchType(
        User $user,
        string $lookingUsers,
        string $whoLooking
    ): QueryBuilder {
        return match ($lookingUsers) {
            WorkUserTypeConstant::AUTHOR->value => $this->userRepository->bySearchAuthors($user, $whoLooking),
            WorkUserTypeConstant::SUPERVISOR->value => $this->userRepository->bySearchSupervisors($user, $whoLooking),
            WorkUserTypeConstant::OPPONENT->value => $this->userRepository->bySearchOpponents($user, $whoLooking),
            WorkUserTypeConstant::CONSULTANT->value => $this->userRepository->bySearchConsultants($user, $whoLooking),
            default => throw new RuntimeException('Type not found'),
        };
    }

    public function hasUserRole(int|string $id, string $role, bool $enabled = true): bool
    {
        $queryBuilder = $this->userRepository->allByUserRole($role, $enabled);
        $rootAliases = $queryBuilder->getRootAliases();
        $rootAlias = $rootAliases[0];
        $select = sprintf('count(%s.id)', $rootAlias);
        $condition = sprintf('%s.id = :id', $rootAlias);

        $count = (int) $queryBuilder
            ->select($select)
            ->andWhere($condition)
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * @return User[]
     */
    public function listLatest(int $limit): array
    {
        /** @var User[] $result */
        $result = $this->userRepository->findBy(
            criteria: [],
            orderBy: ['id' => 'DESC'],
            limit: $limit
        );

        return $result;
    }
}
