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
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class MediaCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediaCategory::class);
    }

    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('media_category');
    }

    public function allByOwner(User $user): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->addSelect('medias')
            ->leftJoin('media_category.medias', 'medias')
            ->where('media_category.owner = :user')
            ->orderBy('media_category.name', Order::Ascending->value)
            ->setParameter('user', $user);
    }

    public function allByOwners(iterable $users): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->where('media_category.owner IN(:users)')
            ->orderBy('media_category.createdAt', Order::Descending->value)
            ->setParameter('users', $users);
    }

    public function allByMediaOwner(User $user): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->leftJoin('media_category.medias', 'medias')
            ->where('medias.owner = :user')
            ->orderBy('media_category.createdAt', Order::Descending->value)
            ->setParameter('user', $user);
    }

    public function allByMediaOwners(iterable $users): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->leftJoin('media_category.medias', 'medias')
            ->where('medias.owner IN(:users)')
            ->orderBy('media_category.createdAt', Order::Descending->value)
            ->setParameter('users', $users);
    }
}
