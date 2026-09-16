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
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class SystemEventRecipientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SystemEventRecipient::class);
    }

    private function createSystemEventRecipientQueryBuilder(): SystemEventRecipientQueryBuilder
    {
        return new SystemEventRecipientQueryBuilder($this->createQueryBuilder('system_event_recipient'));
    }

    public function allByRecipient(User $recipient): QueryBuilder
    {
        return $this->createSystemEventRecipientQueryBuilder()
            ->leftJoinAll()
            ->byRecipient($recipient)
            ->orderByCreatedAt()
            ->getQueryBuilder();
    }

    public function allUnreadByRecipient(User $recipient): QueryBuilder
    {
        return $this->createSystemEventRecipientQueryBuilder()
            ->leftJoinAll()
            ->byRecipient($recipient)
            ->byViewed(false)
            ->orderByCreatedAt()
            ->getQueryBuilder();
    }

    public function updateViewedAll(User $recipient): void
    {
        $this->createSystemEventRecipientQueryBuilder()
            ->getQueryBuilder()
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
        $builder = $this->createSystemEventRecipientQueryBuilder()
            ->leftJoinAll()
            ->byRecipient($systemEventsByTypeRecipient->recipient)
            ->orderByCreatedAt()
            ->paginate($systemEventsByTypeRecipient->limit, $systemEventsByTypeRecipient->offset);

        if ($systemEventsByTypeRecipient->viewed !== null) {
            $builder = $builder->byViewed($systemEventsByTypeRecipient->viewed);
        }

        return $builder->getQueryBuilder();
    }
}
