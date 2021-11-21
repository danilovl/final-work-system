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

namespace App\Model\WorkCategory\Repository;

use App\Model\User\Entity\User;
use App\Model\WorkCategory\Entity\WorkCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class WorkCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkCategory::class);
    }

    public function allByOwner(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('work_category')
            ->addSelect('works')
            ->leftJoin('work_category.works', 'works')
            ->where('work_category.owner = :user')
            ->orderBy('work_category.name', Criteria::ASC)
            ->setParameter('user', $user);
    }
}
