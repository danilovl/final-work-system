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

namespace App\Domain\EventAddress\Service;

use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\EventAddress\Facade\EventAddressFacade;
use App\Domain\User\Entity\User;

readonly class EventAddressSkypeService
{
    public function __construct(private EventAddressFacade $eventAddressFacade) {}

    public function unsetSkypeExcept(User $owner, ?EventAddress $except = null): void
    {
        $skypeAddresses = $this->eventAddressFacade->listSkypeByOwner($owner);

        foreach ($skypeAddresses as $skypeAddress) {
            if ($skypeAddress === $except) {
                continue;
            }

            $skypeAddress->setSkype(false);
        }
    }
}
