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

namespace App\Domain\WorkCategory\Repository;

use App\Domain\User\Entity\User;
use App\Domain\WorkCategory\Entity\WorkCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class WorkCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkCategory::class);
    }

    private function createWorkCategoryQueryBuilder(): WorkCategoryQueryBuilder
    {
        return new WorkCategoryQueryBuilder($this->createQueryBuilder('work_category'));
    }

    public function allByOwner(User $user): QueryBuilder
    {
        return $this->createWorkCategoryQueryBuilder()
            ->selectWorks()
            ->leftJoinWorks()
            ->byOwner($user)
            ->orderByName(Order::Ascending->value)
            ->getQueryBuilder();
    }
}
