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

namespace App\Domain\ResetPassword\Repository;

use App\Domain\ResetPassword\Entity\ResetPassword;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

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

    private function createResetPasswordBuilder(): ResetPasswordQueryBuilder
    {
        return new ResetPasswordQueryBuilder($this->createQueryBuilder('reset_password'));
    }

    public function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('reset_password');
    }

    public function byToken(string $token): QueryBuilder
    {
        return $this->createResetPasswordBuilder()
            ->whereByToken($token)
            ->getQueryBuilder();
    }

    public function mostRecentNonExpiredRequestDate(User $user): QueryBuilder
    {
        return $this->createResetPasswordBuilder()
            ->whereByUser($user)
            ->orderByCreatedAt()
            ->limit(1)
            ->getQueryBuilder();
    }
}
