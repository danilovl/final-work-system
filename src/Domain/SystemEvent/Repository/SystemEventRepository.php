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

namespace App\Domain\SystemEvent\Repository;

use App\Domain\SystemEvent\Entity\SystemEvent;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class SystemEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SystemEvent::class);
    }

    private function createSystemEventBuilder(): SystemEventBuilder
    {
        return new SystemEventBuilder($this->createQueryBuilder('system_event'));
    }

    public function allByRecipient(User $recipient): QueryBuilder
    {
        return $this->createSystemEventBuilder()
            ->leftJoinAll()
            ->distinct()
            ->byRecipient($recipient)
            ->orderByCreatedAt()
            ->getQueryBuilder();
    }

    public function countUnreadByRecipient(User $recipient): QueryBuilder
    {
        return $this->createSystemEventBuilder()
            ->leftJoinAll()
            ->distinct()
            ->selectCount()
            ->byRecipient($recipient)
            ->byRecipientViewed(false)
            ->orderByCreatedAt()
            ->getQueryBuilder();
    }
}
