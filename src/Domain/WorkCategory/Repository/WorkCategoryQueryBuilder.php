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
use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;
use Doctrine\Common\Collections\Order;

class WorkCategoryQueryBuilder extends BaseQueryBuilder
{
    public function selectWorks(): self
    {
        $this->queryBuilder->addSelect('works');

        return $this;
    }

    public function leftJoinWorks(): self
    {
        $this->queryBuilder->leftJoin('work_category.works', 'works');

        return $this;
    }

    public function byOwner(User $user): self
    {
        $this->queryBuilder
            ->andWhere('work_category.owner = :user')
            ->setParameter('user', $user);

        return $this;
    }

    public function orderByName(string $order = Order::Ascending->value): self
    {
        $this->queryBuilder->orderBy('work_category.name', $order);

        return $this;
    }
}
