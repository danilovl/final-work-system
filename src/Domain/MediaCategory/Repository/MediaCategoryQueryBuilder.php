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

use App\Domain\User\Entity\User;
use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;
use Doctrine\Common\Collections\Order;

class MediaCategoryQueryBuilder extends BaseQueryBuilder
{
    public function selectMedias(): self
    {
        $this->queryBuilder->addSelect('medias');

        return $this;
    }

    public function leftJoinMedias(): self
    {
        $this->queryBuilder->leftJoin('media_category.medias', 'medias');

        return $this;
    }

    public function orderByName(string $order = Order::Ascending->value): self
    {
        $this->queryBuilder->orderBy('media_category.name', $order);

        return $this;
    }

    public function orderByCreatedAt(string $order = Order::Descending->value): self
    {
        $this->queryBuilder->orderBy('media_category.createdAt', $order);

        return $this;
    }

    public function byOwner(User $user): self
    {
        $this->queryBuilder
            ->andWhere('media_category.owner = :user')
            ->setParameter('user', $user);

        return $this;
    }

    public function byOwners(iterable $users): self
    {
        $this->queryBuilder
            ->andWhere('media_category.owner IN(:users)')
            ->setParameter('users', $users);

        return $this;
    }

    public function byMediaOwner(User $user): self
    {
        $this->queryBuilder
            ->andWhere('medias.owner = :user')
            ->setParameter('user', $user);

        return $this;
    }

    public function byMediaOwners(iterable $users): self
    {
        $this->queryBuilder
            ->andWhere('medias.owner IN(:users)')
            ->setParameter('users', $users);

        return $this;
    }
}
