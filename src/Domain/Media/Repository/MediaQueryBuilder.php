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

namespace App\Domain\Media\Repository;

use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;
use Doctrine\Common\Collections\Order;

class MediaQueryBuilder extends BaseQueryBuilder
{
    public function selectMimeType(): self
    {
        $this->queryBuilder->addSelect('mime_type');

        return $this;
    }

    public function selectCategories(): self
    {
        $this->queryBuilder->addSelect('categories');

        return $this;
    }

    public function joinMimeType(): self
    {
        $this->queryBuilder->leftJoin('media.mimeType', 'mime_type');

        return $this;
    }

    public function joinCategories(): self
    {
        $this->queryBuilder->leftJoin('media.categories', 'categories');

        return $this;
    }

    public function joinType(): self
    {
        $this->queryBuilder->leftJoin('media.type', 'type');

        return $this;
    }

    public function selectType(): self
    {
        $this->queryBuilder->addSelect('type');

        return $this;
    }

    public function orderByCreatedAt(string $order = Order::Descending->value): self
    {
        $this->queryBuilder->orderBy('media.createdAt', $order);

        return $this;
    }

    public function byUsers(iterable $users): self
    {
        $this->queryBuilder
            ->andWhere($this->queryBuilder->expr()->in('media.owner', ':users'))
            ->setParameter('users', $users);

        return $this;
    }

    public function byNameLike(string $name): self
    {
        $this->queryBuilder
            ->andWhere('media.name LIKE :m_name')
            ->setParameter('m_name', '%' . $name . '%');

        return $this;
    }

    public function byCategoriesIds(iterable $ids): self
    {
        $this->queryBuilder
            ->andWhere($this->queryBuilder->expr()->in('categories.id', ':c_category'))
            ->setParameter('c_category', $ids);

        return $this;
    }

    public function byMimeTypeIds(iterable $ids): self
    {
        $this->queryBuilder
            ->andWhere($this->queryBuilder->expr()->in('mime_type.id', ':m_mimeType'))
            ->setParameter('m_mimeType', $ids);

        return $this;
    }

    public function byType(mixed $type): self
    {
        $this->queryBuilder
            ->andWhere('media.type = :type')
            ->setParameter('type', $type);

        return $this;
    }

    public function byTypeAliasEquals(mixed $type): self
    {
        $this->queryBuilder
            ->andWhere('type = :type')
            ->setParameter('type', $type);

        return $this;
    }

    public function byActive(bool $active): self
    {
        $this->queryBuilder
            ->andWhere('media.active = :active')
            ->setParameter('active', $active);

        return $this;
    }

    public function byWork(Work $work): self
    {
        $this->queryBuilder
            ->andWhere('media.work = :work')
            ->setParameter('work', $work);

        return $this;
    }

    public function byOwner(User $user): self
    {
        $this->queryBuilder
            ->andWhere('media.owner = :user')
            ->setParameter('user', $user);

        return $this;
    }
}
