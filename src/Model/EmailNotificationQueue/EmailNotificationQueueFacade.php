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

namespace App\Model\EmailNotificationQueue;

use App\Entity\EmailNotificationQueue;
use App\Repository\EmailNotificationQueueRepository;

class EmailNotificationQueueFacade
{
    public function __construct(private EmailNotificationQueueRepository $emailNotificationQueueRepository)
    {
    }

    public function getOneReadyForSender(): ?EmailNotificationQueue
    {
        return $this->emailNotificationQueueRepository
            ->oneReadyForSender()
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}