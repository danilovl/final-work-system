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

use App\Application\Exception\RuntimeException;
use App\Domain\User\Constant\{
    UserRoleConstant
};
use App\Domain\User\Entity\User;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
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
        $queryBuilder = $this->createQueryBuilder('user')
            ->addSelect('work, groups')
            ->leftJoin('user.groups', 'groups');

        switch ($type) {
            case WorkUserTypeConstant::AUTHOR->value:
                $queryBuilder->join('user.authorWorks', 'work')
                    ->where('work.supervisor = :supervisor');

                break;
            case WorkUserTypeConstant::OPPONENT->value:
                $queryBuilder->join('user.opponentWorks', 'work')
                    ->where('work.supervisor = :supervisor');

                break;
            case WorkUserTypeConstant::CONSULTANT->value:
                $queryBuilder->join('user.consultantWorks', 'work')
                    ->where('work.supervisor = :supervisor');

                break;
        }

        $queryBuilder
            ->orderBy('user.lastname', Order::Ascending->value)
            ->groupBy('user.id')
            ->setParameter('supervisor', $user);

        if ($workStatus instanceof WorkStatus) {
            $queryBuilder->andWhere('work.status = :status');
            $queryBuilder->setParameter('status', $workStatus);
        } elseif (is_iterable($workStatus)) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('work.status', ':statuses'));
            $queryBuilder->setParameter('statuses', $workStatus);
        }

        return $queryBuilder;
    }

    public function bySearchAuthors(
        User $user,
        string $type,
        ?array $workStatus = null
    ): QueryBuilder {
        $queryBuilder = $this->createQueryBuilder('user')
            ->join('user.authorWorks', 'work');

        switch ($type) {
            case WorkUserTypeConstant::SUPERVISOR->value:
                $queryBuilder->where('work.supervisor = :user');

                break;
            case WorkUserTypeConstant::OPPONENT->value:
                $queryBuilder->where('work.opponent = :user');

                break;
            case WorkUserTypeConstant::CONSULTANT->value:
                $queryBuilder->where('work.consultant = :user');

                break;
            default:
                throw new RuntimeException('Type not found');
        }

        if ($workStatus !== null) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('work.status', ':statuses'));
            $queryBuilder->setParameter('statuses', $workStatus);
        }

        $queryBuilder
            ->orderBy('user.lastname', Order::Ascending->value)
            ->groupBy('user.id')
            ->setParameter('user', $user);

        return $queryBuilder;
    }

    public function bySearchSupervisors(
        User $user,
        string $type,
        ?array $workStatus = null
    ): QueryBuilder {
        $queryBuilder = $this->createQueryBuilder('user')
            ->join('user.supervisorWorks', 'work');

        switch ($type) {
            case WorkUserTypeConstant::AUTHOR->value:
                $queryBuilder->where('work.author = :user');

                break;
            case WorkUserTypeConstant::OPPONENT->value:
                $queryBuilder->where('work.opponent = :user');

                break;
            case WorkUserTypeConstant::CONSULTANT->value:
                $queryBuilder->where('work.consultant = :user');

                break;
            default:
                throw new RuntimeException('Type not found');
        }

        if ($workStatus !== null) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('work.status', ':statuses'));
            $queryBuilder->setParameter('statuses', $workStatus);
        }

        $queryBuilder
            ->orderBy('user.lastname', Order::Ascending->value)
            ->groupBy('user.id')
            ->setParameter('user', $user);

        return $queryBuilder;
    }

    public function bySearchOpponents(
        User $user,
        string $type,
        ?array $workStatus = null
    ): QueryBuilder {
        $queryBuilder = $this->createQueryBuilder('user')
            ->join('user.opponentWorks', 'work');

        switch ($type) {
            case WorkUserTypeConstant::AUTHOR->value:
                $queryBuilder->where('work.author = :user');

                break;
            case WorkUserTypeConstant::SUPERVISOR->value:
                $queryBuilder->where('work.supervisor = :user');

                break;
            case WorkUserTypeConstant::CONSULTANT->value:
                $queryBuilder->where('work.consultant = :user');

                break;
            default:
                throw new RuntimeException('Type not found');
        }

        if ($workStatus !== null) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('work.status', ':statuses'));
            $queryBuilder->setParameter('statuses', $workStatus);
        }

        $queryBuilder
            ->orderBy('user.lastname', Order::Ascending->value)
            ->groupBy('user.id')
            ->setParameter('user', $user);

        return $queryBuilder;
    }

    public function bySearchConsultants(
        User $user,
        string $type,
        ?array $workStatus = null
    ): QueryBuilder {
        $queryBuilder = $this->createQueryBuilder('user')
            ->join('user.consultantWorks', 'work');

        switch ($type) {
            case WorkUserTypeConstant::AUTHOR->value:
                $queryBuilder->where('work.author = :user');

                break;
            case WorkUserTypeConstant::OPPONENT->value:
                $queryBuilder->where('work.opponent = :user');

                break;
            case WorkUserTypeConstant::SUPERVISOR->value:
                $queryBuilder->where('work.supervisor = :user');

                break;
            default:
                throw new RuntimeException('Type not found');
        }

        if ($workStatus !== null) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('work.status', ':statuses'));
            $queryBuilder->setParameter('statuses', $workStatus);
        }

        $queryBuilder
            ->orderBy('user.lastname', Order::Ascending->value)
            ->groupBy('user.id')
            ->setParameter('user', $user);

        return $queryBuilder;
    }

    public function unusedUser(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('user')
            ->addSelect('groups')
            ->leftJoin('user.groups', 'groups')
            ->leftJoin('user.authorWorks', 'author_work')
            ->leftJoin('user.opponentWorks', 'opponent_work')
            ->leftJoin('user.consultantWorks', 'consultant_work')
            ->where('user != :user')
            ->andWhere('user.roles LIKE :roleStudent')
            ->orWhere('user.roles LIKE :roleOpponent')
            ->orWhere('user.roles LIKE :roleConsultant')
            ->andWhere('author_work is NULL')
            ->andWhere('opponent_work is NULL')
            ->andWhere('consultant_work is NULL')
            ->orderBy('user.lastname', Order::Ascending->value)
            ->setParameter('user', $user)
            ->setParameter('roleStudent', '%' . UserRoleConstant::STUDENT->value . '%')
            ->setParameter('roleOpponent', '%' . UserRoleConstant::OPPONENT->value . '%')
            ->setParameter('roleConsultant', '%' . UserRoleConstant::CONSULTANT->value . '%');
    }

    public function allByUserRole(string $role, bool $enable = true): QueryBuilder
    {
        return $this->createQueryBuilder('user')
            ->where('user.roles LIKE :roles')
            ->andWhere('user.enabled = :enable')
            ->addOrderBy('user.lastname', Order::Ascending->value)
            ->addOrderBy('user.firstname', Order::Ascending->value)
            ->setParameter('roles', "%{$role}%")
            ->setParameter('enable', $enable);
    }

    public function oneByUsername(string $username, ?bool $enable = null): QueryBuilder
    {
        $builder = $this->createQueryBuilder('user')
            ->andWhere('user.username = :username')
            ->setParameter('username', $username);

        if ($enable !== null) {
            $builder->andWhere('user.enabled = :enable')
                ->setParameter('enable', $enable);
        }

        return $builder;
    }

    public function oneByEmail(string $email, ?bool $enable = null): QueryBuilder
    {
        $builder = $this->createQueryBuilder('user')
            ->andWhere('user.email = :email')
            ->setParameter('email', $email);

        if ($enable !== null) {
            $builder->andWhere('user.enabled = :enable')
                ->setParameter('enable', $enable);
        }

        return $builder;
    }

    public function oneByToken(string $username, string $token, ?bool $enable = null): QueryBuilder
    {
        $builder = $this->createQueryBuilder('user')
            ->andWhere('user.token = :token')
            ->andWhere('user.username = :username')
            ->setParameter('token', $token)
            ->setParameter('username', $username);

        if ($enable !== null) {
            $builder->andWhere('user.enabled = :enable')
                ->setParameter('enable', $enable);
        }

        return $builder;
    }
}
