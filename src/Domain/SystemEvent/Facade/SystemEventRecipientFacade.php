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

namespace App\Domain\SystemEvent\Facade;

use App\Domain\SystemEvent\DTO\Repository\SystemEventRepositoryDTO;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Domain\SystemEventRecipient\Repository\SystemEventRecipientRepository;
use App\Domain\User\Entity\User;
use Doctrine\ORM\Query;

readonly class SystemEventRecipientFacade
{
    public function __construct(private SystemEventRecipientRepository $systemEventRecipientRepository) {}

    public function queryRecipientsByUser(User $user): Query
    {
        return $this->systemEventRecipientRepository
            ->allByRecipient($user)
            ->getQuery();
    }

    /**
     * @return SystemEventRecipient[]
     */
    public function getUnreadSystemEventsByRecipient(User $user, ?int $limit = null): array
    {
        $systemEvents = $this->systemEventRecipientRepository
            ->allUnreadByRecipient($user);

        if ($limit !== null) {
            $systemEvents->setMaxResults($limit);
        }

        /** @var SystemEventRecipient[] $result */
        $result = $systemEvents->getQuery()->getResult();

        return $result;
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

    public function querySystemEventsByStatus(SystemEventRepositoryDTO $systemEventRepositoryData): Query
    {
        return $this->systemEventRecipientRepository
            ->systemEventsByStatus($systemEventRepositoryData)
            ->getQuery();
    }
}
