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


namespace App\Repository;

use App\Entity\{
    User,
    ResetPassword
};
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method ResetPassword|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResetPassword|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResetPassword[]    all()
 * @method ResetPassword[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResetPasswordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetPassword::class);
    }

    public function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('reset_password');
    }

    public function byToken(string $token): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->andWhere('reset_password.hashedToken =:token')
            ->setParameter('token', $token);
    }

    public function mostRecentNonExpiredRequestDate(User $user): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->where('reset_password.user = :user')
            ->setParameter('user', $user)
            ->orderBy('reset_password.createdAt', Criteria::DESC)
            ->setMaxResults(1);
    }
}
