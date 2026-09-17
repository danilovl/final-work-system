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

namespace App\Domain\User\Repository;

use App\Domain\User\Constant\UserRoleConstant;
use App\Domain\User\Entity\User;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\{
    PasswordAuthenticatedUserInterface,
    PasswordUpgraderInterface
};

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    all()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    private function createUserQueryBuilder(): UserQueryBuilder
    {
        return new UserQueryBuilder($this->createQueryBuilder('user'));
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $user->setSalt(null);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function bySupervisor(
        User $user,
        string $type,
        iterable|WorkStatus|null $workStatus = null
    ): QueryBuilder {
        return $this->createUserQueryBuilder()
            ->bySupervisor($user, $type, $workStatus)
            ->getQueryBuilder();
    }

    public function bySearchAuthors(
        User $user,
        string $type,
        ?array $workStatus = null
    ): QueryBuilder {
        return $this->createUserQueryBuilder()
            ->bySearchAuthors($user, $type, $workStatus)
            ->getQueryBuilder();
    }

    public function bySearchSupervisors(
        User $user,
        string $type,
        ?array $workStatus = null
    ): QueryBuilder {
        return $this->createUserQueryBuilder()
            ->bySearchSupervisors($user, $type, $workStatus)
            ->getQueryBuilder();
    }

    public function bySearchOpponents(
        User $user,
        string $type,
        ?array $workStatus = null
    ): QueryBuilder {
        return $this->createUserQueryBuilder()
            ->bySearchOpponents($user, $type, $workStatus)
            ->getQueryBuilder();
    }

    public function bySearchConsultants(
        User $user,
        string $type,
        ?array $workStatus = null
    ): QueryBuilder {
        return $this->createUserQueryBuilder()
            ->bySearchConsultants($user, $type, $workStatus)
            ->getQueryBuilder();
    }

    public function unusedUser(User $user): QueryBuilder
    {
        return $this->createUserQueryBuilder()
            ->joinGroups()
            ->leftJoinWorks()
            ->filterNotUser($user)
            ->filterRoleLikeAny([
                UserRoleConstant::STUDENT->value,
                UserRoleConstant::OPPONENT->value,
                UserRoleConstant::CONSULTANT->value,
            ])
            ->filterWithoutAnyWorks()
            ->orderByName()
            ->getQueryBuilder();
    }

    public function allByUserRole(string $role, bool $enable = true): QueryBuilder
    {
        return $this->createUserQueryBuilder()
            ->filterRoleLike($role)
            ->filterEnabled($enable)
            ->orderByName()
            ->getQueryBuilder();
    }

    public function oneByUsername(string $username, ?bool $enable = null): QueryBuilder
    {
        return $this->createUserQueryBuilder()
            ->byUsername($username)
            ->filterEnabled($enable)
            ->getQueryBuilder();
    }

    public function oneByEmail(string $email, ?bool $enable = null): QueryBuilder
    {
        return $this->createUserQueryBuilder()
            ->byEmail($email)
            ->filterEnabled($enable)
            ->getQueryBuilder();
    }

    public function oneByToken(string $username, string $token, ?bool $enable = null): QueryBuilder
    {
        return $this->createUserQueryBuilder()
            ->byToken($token)
            ->byUsername($username)
            ->filterEnabled($enable)
            ->getQueryBuilder();
    }
}
