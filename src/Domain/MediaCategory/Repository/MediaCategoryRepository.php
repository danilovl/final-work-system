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

namespace App\Domain\MediaCategory\Repository;

use App\Domain\MediaCategory\Entity\MediaCategory;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class MediaCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediaCategory::class);
    }

    private function createMediaCategoryQueryBuilder(): MediaCategoryQueryBuilder
    {
        return new MediaCategoryQueryBuilder($this->createQueryBuilder('media_category'));
    }

    public function allByOwner(User $user): QueryBuilder
    {
        return $this->createMediaCategoryQueryBuilder()
            ->selectMedias()
            ->leftJoinMedias()
            ->whereByOwner($user)
            ->orderByName()
            ->getQueryBuilder();
    }

    public function allByOwners(iterable $users): QueryBuilder
    {
        return $this->createMediaCategoryQueryBuilder()
            ->whereByOwners($users)
            ->orderByCreatedAt()
            ->getQueryBuilder();
    }

    public function allByMediaOwner(User $user): QueryBuilder
    {
        return $this->createMediaCategoryQueryBuilder()
            ->leftJoinMedias()
            ->whereByMediaOwner($user)
            ->orderByCreatedAt()
            ->getQueryBuilder();
    }

    public function allByMediaOwners(iterable $users): QueryBuilder
    {
        return $this->createMediaCategoryQueryBuilder()
            ->leftJoinMedias()
            ->whereByMediaOwners($users)
            ->orderByCreatedAt()
            ->getQueryBuilder();
    }
}
