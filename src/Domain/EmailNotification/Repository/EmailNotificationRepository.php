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

use App\Domain\EmailNotification\Entity\EmailNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class EmailNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailNotification::class);
    }

    public function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('email_notification');
    }

    public function oneReadyForSender(): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->andWhere('email_notification.sendedAt IS NULL')
            ->orderBy('email_notification.createdAt', Criteria::ASC);
    }

    public function byUuid(string $uuid): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->andWhere('email_notification.uuid = :uuid')
            ->setParameter('uuid', $uuid);
    }
}
