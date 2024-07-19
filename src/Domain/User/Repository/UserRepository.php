<?php

namespace App\Domain\User\Repository;

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

        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function bySupervisor(
        User $user,
        string $type,
        iterable|WorkStatus $workStatus = null
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
