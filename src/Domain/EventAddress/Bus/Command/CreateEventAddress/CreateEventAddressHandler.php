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

namespace App\Domain\EventAddress\Bus\Command\CreateEventAddress;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\EventAddress\Factory\EventAddressFactory;

readonly class CreateEventAddressHandler implements CommandHandlerInterface
{
    public function __construct(private EventAddressFactory $eventAddressFactory) {}

    public function __invoke(CreateEventAddressCommand $command): EventAddress
    {
        $eventAddressModel = $command->eventAddressModel;

        return $this->eventAddressFactory->flushFromModel($eventAddressModel);
    }
}
