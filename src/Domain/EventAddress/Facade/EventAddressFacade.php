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

namespace App\Domain\EventAddress\Facade;

use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\EventAddress\Repository\EventAddressRepository;
use App\Domain\User\Entity\User;

class EventAddressFacade
{
    public function __construct(private readonly EventAddressRepository $eventAddressRepository) {}

    public function getSkypeByOwner(User $user): ?EventAddress
    {
        return $this->eventAddressRepository
            ->skypeByOwner($user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
