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

namespace App\Domain\EventAddress\Bus\Command\SwitchEventAddressSkype;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\EventAddress\Service\EventAddressSkypeService;
use App\Infrastructure\Service\EntityManagerService;

readonly class SwitchEventAddressSkypeHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private EventAddressSkypeService $eventAddressSkypeService
    ) {}

    public function __invoke(SwitchEventAddressSkypeCommand $command): void
    {
        $eventAddress = $command->eventAddress;
        $isSkype = !$eventAddress->isSkype();

        if ($isSkype) {
            $this->eventAddressSkypeService->unsetSkypeExcept(
                owner: $eventAddress->getOwner(),
                except: $eventAddress
            );
        }

        $eventAddress->setSkype($isSkype);
        $this->entityManagerService->flush();
    }
}
