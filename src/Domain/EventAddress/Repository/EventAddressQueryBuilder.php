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

namespace App\Domain\EventAddress\Repository;

use App\Domain\User\Entity\User;
use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;
use SortDirection;

class EventAddressQueryBuilder extends BaseQueryBuilder
{
    public function whereByOwner(User $user): self
    {
        $this->queryBuilder
            ->andWhere('event_address.owner = :user')
            ->setParameter('user', $user);

        return $this;
    }

    public function orderById(SortDirection $order = SortDirection::Ascending): self
    {
        $this->queryBuilder->orderBy('event_address.id', $order);

        return $this;
    }

    public function whereBySkypeOwner(User $user): self
    {
        $this->queryBuilder
            ->andWhere('event_address.skype = :skype')
            ->andWhere('event_address.owner = :user')
            ->setParameter('skype', true)
            ->setParameter('user', $user);

        return $this;
    }
}
