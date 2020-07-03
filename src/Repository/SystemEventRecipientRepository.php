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

namespace App\Repository;

use App\Entity\{
    User,
    SystemEventRecipient
};
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class SystemEventRecipientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SystemEventRecipient::class);
    }

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

    public function allByRecipient(User $recipient): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->where('system_event_recipient.recipient = :recipient')
            ->orderBy('systemEvent.createdAt', Criteria::DESC)
            ->setParameter('recipient', $recipient);
    }

    public function allUnreadByRecipient(User $recipient): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->where('system_event_recipient.recipient = :recipient')
            ->andWhere('system_event_recipient.viewed = :viewed')
            ->orderBy('systemEvent.createdAt', Criteria::DESC)
            ->setParameter('viewed', false)
            ->setParameter('recipient', $recipient);
    }

    public function updateViewedAll(User $recipient): void
    {
        $this->createQueryBuilder('system_event_recipient')
            ->update()
            ->set('system_event_recipient.viewed', true)
            ->andWhere('system_event_recipient.recipient = :recipient')
            ->andWhere('system_event_recipient.viewed = :viewed')
            ->setParameter('viewed', false)
            ->setParameter('recipient', $recipient)
            ->getQuery()
            ->execute();
    }
}
