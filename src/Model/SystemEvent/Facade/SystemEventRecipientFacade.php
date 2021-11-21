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

use App\Model\SystemEventRecipient\Repository\SystemEventRecipientRepository;
use Doctrine\ORM\Query;
use App\Model\User\Entity\User;

class SystemEventRecipientFacade
{
    public function __construct(private SystemEventRecipientRepository $systemEventRecipientRepository)
    {
    }

    public function queryRecipientsByUser(User $user): Query
    {
        return $this->systemEventRecipientRepository
            ->allByRecipient($user)
            ->getQuery();
    }

    public function getUnreadSystemEventsByRecipient(User $user, int $limit = null): array
    {
        $systemEvents = $this->systemEventRecipientRepository
            ->allUnreadByRecipient($user);

        if ($limit !== null) {
            $systemEvents->setMaxResults($limit);
        }

        return $systemEvents->getQuery()->getResult();
    }

    public function queryRecipientsQueryByUser(User $recipient): Query
    {
        return $this->systemEventRecipientRepository
            ->allByRecipient($recipient)
            ->getQuery();
    }

    public function updateViewedAll(User $recipient): void
    {
        $this->systemEventRecipientRepository->updateViewedAll($recipient);
    }
}
