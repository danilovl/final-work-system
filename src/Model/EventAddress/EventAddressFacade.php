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

namespace App\Model\EventAddress;

use App\Entity\EventAddress;
use App\Repository\EventAddressRepository;
use App\Entity\User;

class EventAddressFacade
{
    private EventAddressRepository $eventAddressRepository;

    public function __construct(EventAddressRepository $eventAddressRepository)
    {
        $this->eventAddressRepository = $eventAddressRepository;
    }

    public function getSkypeByOwner(User $user): ?EventAddress
    {
        return $this->eventAddressRepository
            ->skypeByOwner($user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
