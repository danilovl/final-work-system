<?php

namespace App\Repository;

use Doctrine\Common\Collections\Criteria;
use App\Constant\{
    UserRoleConstant,
    WorkUserTypeConstant
};
use App\Entity\{
    User,
    WorkStatus
};
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\{
    UserInterface,
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

    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function bySupervisor(
        User $user,
        string $type,
        $workStatus = null
    ): QueryBuilder {
        $queryBuilder = $this->createQueryBuilder('user')
            ->addSelect('work');

        switch ($type) {
            case WorkUserTypeConstant::AUTHOR:
                $queryBuilder->join('user.authorWorks', 'work')
                    ->where('work.supervisor = :supervisor');
                break;
            case WorkUserTypeConstant::OPPONENT:
                $queryBuilder->join('user.opponentWorks', 'work')
                    ->where('work.supervisor = :supervisor');
                break;
            case WorkUserTypeConstant::CONSULTANT:
                $queryBuilder->join('user.consultantWorks', 'work')
                    ->where('work.supervisor = :supervisor');
                break;
        }

        $queryBuilder
            ->orderBy('user.lastname', Criteria::ASC)
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
            ->orderBy('user.lastname', Criteria::ASC)
            ->setParameter('user', $user)
            ->setParameter('roleStudent', '%' . UserRoleConstant::STUDENT . '%')
            ->setParameter('roleOpponent', '%' . UserRoleConstant::OPPONENT . '%')
            ->setParameter('roleConsultant', '%' . UserRoleConstant::CONSULTANT . '%');
    }

    public function allByUserRole(string $role, bool $enable = true): QueryBuilder
    {
        return $this->createQueryBuilder('user')
            ->where('user.roles LIKE :roles')
            ->andWhere('user.enabled = :enable')
            ->addOrderBy('user.lastname', Criteria::ASC)
            ->addOrderBy('user.firstname', Criteria::ASC)
            ->setParameter('roles', "%{$role}%")
            ->setParameter('enable', $enable);
    }

    public function byUsername(
        string $username,
        ?bool $enable = null
    ): ?QueryBuilder {
        $builder = $this->createQueryBuilder('user')
            ->andWhere('user.username = :username')
            ->setParameter('username', $username);

        if ($enable !== null) {
            $builder->andWhere('user.enabled = :enable')
                ->setParameter('enable', $enable);
        }

        return $builder;
    }

    public function byEmail(
        string $email,
        ?bool $enable = null
    ): ?QueryBuilder {
        $builder = $this->createQueryBuilder('user')
            ->andWhere('user.email = :email')
            ->setParameter('email', $email);

        if ($enable !== null) {
            $builder->andWhere('user.enabled = :enable')
                ->setParameter('enable', $enable);
        }
        return $builder;
    }
}
