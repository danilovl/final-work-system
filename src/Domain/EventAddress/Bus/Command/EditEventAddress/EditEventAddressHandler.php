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

namespace App\Domain\EventAddress\Bus\Command\EditEventAddress;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\EventAddress\Factory\EventAddressFactory;
use App\Domain\EventAddress\Service\EventAddressSkypeService;

readonly class EditEventAddressHandler implements CommandHandlerInterface
{
    public function __construct(
        private EventAddressFactory $eventAddressFactory,
        private EventAddressSkypeService $eventAddressSkypeService
    ) {}

    public function __invoke(EditEventAddressCommand $command): EventAddress
    {
        if ($command->eventAddressModel->skype) {
            $this->eventAddressSkypeService->unsetSkypeExcept(
                owner: $command->eventAddressModel->owner,
                except: $command->eventAddress
            );
        }

        return $this->eventAddressFactory->flushFromModel(
            $command->eventAddressModel,
            $command->eventAddress
        );
    }
}
