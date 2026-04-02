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

namespace App\Domain\EmailNotification\Facade;

use App\Domain\EmailNotification\Entity\EmailNotification;
use App\Domain\EmailNotification\Repository\EmailNotificationRepository;

readonly class EmailNotificationFacade
{
    public function __construct(private EmailNotificationRepository $emailNotificationRepository) {}

    public function findReadyForSender(): ?EmailNotification
    {
        /** @var EmailNotification|null $result */
        $result = $this->emailNotificationRepository
            ->oneReadyForSender()
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    public function findByUuid(string $uuid): ?EmailNotification
    {
        /** @var EmailNotification|null $result */
        $result = $this->emailNotificationRepository
            ->byUuid($uuid)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }
}
