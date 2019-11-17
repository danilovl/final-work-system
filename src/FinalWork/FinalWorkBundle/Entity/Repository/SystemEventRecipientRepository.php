<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace FinalWork\FinalWorkBundle\Entity\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\{
    QueryBuilder,
    EntityRepository
};
use FinalWork\SonataUserBundle\Entity\User;

class SystemEventRecipientRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('system_event_recipient')
            ->leftJoin('system_event_recipient.systemEvent', 'systemEvent')
            ->leftJoin('systemEvent.type', 'type')
            ->leftJoin('systemEvent.conversation', 'conversation')
            ->leftJoin('systemEvent.event', 'event')
            ->leftJoin('systemEvent.owner', 'owner')
            ->leftJoin('systemEvent.task', 'task')
            ->leftJoin('systemEvent.work', 'work')
            ->setCacheable(true);
    }

    /**
     * @param User $recipient
     * @return QueryBuilder
     */
    public function findAllByRecipient(User $recipient): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->where('system_event_recipient.recipient = :recipient')
            ->orderBy('systemEvent.createdAt', Criteria::DESC)
            ->setParameter('recipient', $recipient);
    }

    /**
     * @param User $recipient
     * @return QueryBuilder
     */
    public function findAllUnreadByRecipient(User $recipient): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->where('system_event_recipient.recipient = :recipient')
            ->andWhere('system_event_recipient.viewed = :viewed')
            ->orderBy('systemEvent.createdAt', Criteria::DESC)
            ->setParameter('viewed', false)
            ->setParameter('recipient', $recipient);
    }
}
