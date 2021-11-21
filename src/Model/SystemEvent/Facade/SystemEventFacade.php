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

namespace App\Model\SystemEvent\Facade;

use App\Model\SystemEvent\Repository\SystemEventRepository;
use App\Model\User\Entity\User;
use DateTime;

class SystemEventFacade
{
    public function __construct(private SystemEventRepository $systemEventRepository)
    {
    }

    public function getUnreadSystemEventsByRecipient(User $user, int $limit = null): array
    {
        $systemEvents = $this->systemEventRepository
            ->allByRecipient($user);

        if ($limit !== null) {
            $systemEvents->setMaxResults($limit);
        }

        return $systemEvents->getQuery()->getResult();
    }

    public function getTotalUnreadSystemEventsByRecipient(User $user): ?int
    {
        return (int) $this->systemEventRepository
            ->countUnreadByRecipient($user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTotalUnreadSystemEventsAfterDateByRecipient(
        User $user,
        DateTime $date
    ): ?int {
        return (int) $this->systemEventRepository
            ->countUnreadByRecipient($user)
            ->andWhere('system_event.createdAt >= :afterDate')
            ->setParameter('afterDate', $date)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function isUnreadSystemEventsByRecipient(User $user): bool
    {
        return $this->getTotalUnreadSystemEventsByRecipient($user) > 0;
    }
}
