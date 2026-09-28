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

namespace App\Domain\EmailNotification\Repository;

use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;
use Doctrine\Common\Collections\Order;

class EmailNotificationQueryBuilder extends BaseQueryBuilder
{
    public function oneReadyForSender(): self
    {
        $this->queryBuilder
            ->andWhere('email_notification.sendedAt IS NULL')
            ->orderBy('email_notification.createdAt', Order::Ascending->value);

        return $this;
    }

    public function whereByUuid(string $uuid): self
    {
        $this->queryBuilder
            ->andWhere('email_notification.uuid = :uuid')
            ->setParameter('uuid', $uuid);

        return $this;
    }

    public function orderByCreatedAt(string $order = Order::Descending->value): self
    {
        $this->queryBuilder->orderBy('email_notification.createdAt', $order);

        return $this;
    }
}
