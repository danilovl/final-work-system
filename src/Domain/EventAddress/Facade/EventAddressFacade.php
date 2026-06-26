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

readonly class EventAddressFacade
{
    public function __construct(private EventAddressRepository $eventAddressRepository) {}

    public function findById(int $id): ?EventAddress
    {
        /** @var EventAddress|null $result */
        $result = $this->eventAddressRepository->find($id);

        return $result;
    }

    public function findSkypeByOwner(User $user): ?EventAddress
    {
        /** @var EventAddress|null $result */
        $result = $this->eventAddressRepository
            ->skypeByOwner($user)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }
}
