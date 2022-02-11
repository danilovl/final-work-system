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

namespace App\Domain\SystemEvent\Repository;

use App\Domain\SystemEvent\Entity\SystemEvent;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class SystemEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SystemEvent::class);
    }

    private function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('system_event')
            ->leftJoin('system_event.type', 'type')
            ->leftJoin('system_event.recipient', 'recipient')
            ->leftJoin('system_event.conversation', 'conversation')
            ->leftJoin('system_event.event', 'event')
            ->leftJoin('system_event.owner', 'owner')
            ->leftJoin('system_event.task', 'task')
            ->leftJoin('system_event.work', 'work');
    }

    public function allByRecipient(User $recipient): QueryBuilder
    {
        return $this->createBaseQueryBuilder()
            ->distinct()
            ->where('recipient.recipient = :recipient')
            ->orderBy('system_event.createdAt', Criteria::DESC)
            ->setParameter('recipient', $recipient)
            ->setCacheable(true);
    }

    public function countUnreadByRecipient(User $recipient): QueryBuilder
    {
        return $this->createBaseQueryBuilder()
            ->distinct()
            ->select('count(system_event.id)')
            ->where('recipient.recipient = :recipient')
            ->andWhere('recipient.viewed = :viewed')
            ->orderBy('system_event.createdAt', Criteria::DESC)
            ->setParameter('recipient', $recipient)
            ->setParameter('viewed', false);
    }
}
