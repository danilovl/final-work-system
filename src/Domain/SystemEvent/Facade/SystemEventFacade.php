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

use App\Domain\SystemEvent\Repository\SystemEventRepository;
use App\Domain\User\Entity\User;

readonly class SystemEventFacade
{
    public function __construct(private SystemEventRepository $systemEventRepository) {}

    public function getTotalUnreadSystemEventsByRecipient(User $user): ?int
    {
        return (int) $this->systemEventRepository
            ->countUnreadByRecipient($user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function isUnreadSystemEventsByRecipient(User $user): bool
    {
        return $this->getTotalUnreadSystemEventsByRecipient($user) > 0;
    }
}
