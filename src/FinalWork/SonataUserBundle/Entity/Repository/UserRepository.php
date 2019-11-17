<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace FinalWork\SonataUserBundle\Entity\Repository;

use Doctrine\ORM\{
    QueryBuilder,
    EntityRepository
};
use FinalWork\FinalWorkBundle\Constant\{
    UserRoleConstant,
    WorkUserTypeConstant
};
use FinalWork\FinalWorkBundle\Entity\WorkStatus;
use FinalWork\SonataUserBundle\Entity\User;

class UserRepository extends EntityRepository
{
    /**
     * @param User $user
     * @param string $type
     * @param WorkStatus|iterable|null $workStatus
     * @return QueryBuilder
     */
    public function findUserBySupervisor(
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
            ->orderBy('user.lastname', 'ASC')
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

    /**
     * @param User $user
     * @return QueryBuilder
     */
    public function findUnusedUser(User $user): QueryBuilder
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
            ->orderBy('user.lastname', 'ASC')
            ->setParameter('user', $user)
            ->setParameter('roleStudent', '%' . UserRoleConstant::STUDENT . '%')
            ->setParameter('roleOpponent', '%' . UserRoleConstant::OPPONENT . '%')
            ->setParameter('roleConsultant', '%' . UserRoleConstant::CONSULTANT . '%');
    }

    /**
     * @param string $role
     * @param bool $enable
     * @return QueryBuilder
     */
    public function findAllByUserRole(string $role, bool $enable = true): QueryBuilder
    {
        return $this->createQueryBuilder('user')
            ->where('user.roles LIKE :roles')
            ->andWhere('user.enabled = :enable')
            ->addOrderBy('user.lastname', 'ASC')
            ->addOrderBy('user.firstname', 'ASC')
            ->setParameter('roles', '%' . $role . '%')
            ->setParameter('enable', $enable);
    }
}
