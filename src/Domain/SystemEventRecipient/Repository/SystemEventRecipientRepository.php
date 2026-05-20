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

namespace App\Domain\SystemEventRecipient\Repository;

use App\Domain\SystemEvent\DTO\Repository\SystemEventRepositoryDTO;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class SystemEventRecipientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SystemEventRecipient::class);
    }

    private function baseQueryBuilder(?SystemEventRepositoryDTO $systemEventsByTypeRecipient = null): QueryBuilder
    {
        $builder = $this->createQueryBuilder('system_event_recipient')
            ->leftJoin('system_event_recipient.systemEvent', 'systemEvent')
            ->leftJoin('systemEvent.type', 'type')
            ->leftJoin('systemEvent.conversation', 'conversation')
            ->leftJoin('systemEvent.event', 'event')
            ->leftJoin('systemEvent.owner', 'owner')
            ->leftJoin('systemEvent.task', 'task')
            ->leftJoin('systemEvent.work', 'work');

        if ($systemEventsByTypeRecipient !== null) {
            if ($systemEventsByTypeRecipient->limit !== null) {
                $builder->setMaxResults($systemEventsByTypeRecipient->limit);
            }

            if ($systemEventsByTypeRecipient->offset !== null) {
                $builder->setFirstResult($systemEventsByTypeRecipient->offset);
            }
        }

        return $builder;
    }

    public function allByRecipient(User $recipient): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->where('system_event_recipient.recipient = :recipient')
            ->orderBy('systemEvent.createdAt', Order::Descending->value)
            ->setParameter('recipient', $recipient);
    }

    public function allUnreadByRecipient(User $recipient): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->where('system_event_recipient.recipient = :recipient')
            ->andWhere('system_event_recipient.viewed = :viewed')
            ->orderBy('systemEvent.createdAt', Order::Descending->value)
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

    public function systemEventsByStatus(SystemEventRepositoryDTO $systemEventsByTypeRecipient): QueryBuilder
    {
        $builder = $this->baseQueryBuilder()
            ->andWhere('system_event_recipient.recipient = :recipient')
            ->orderBy('systemEvent.createdAt', Order::Descending->value)
            ->setParameter('recipient', $systemEventsByTypeRecipient->recipient);

        if ($systemEventsByTypeRecipient->viewed !== null) {
            $builder->andWhere('system_event_recipient.viewed = :viewed')
                ->setParameter('viewed', $systemEventsByTypeRecipient->viewed);
        }

        return $builder;
    }
}
