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

namespace App\Domain\EventAddress\Http\Ajax;

use App\Infrastructure\Constant\AjaxJsonTypeConstant;
use App\Infrastructure\Interfaces\Bus\CommandBusInterface;
use App\Domain\EventAddress\Bus\Command\SwitchEventAddressSkype\SwitchEventAddressSkypeCommand;
use App\Domain\EventAddress\Entity\EventAddress;
use App\Infrastructure\Service\RequestService;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class EventAddressSwitchSkypeHandle
{
    public function __construct(
        private RequestService $requestService,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(EventAddress $eventAddress): JsonResponse
    {
        $command = SwitchEventAddressSkypeCommand::create($eventAddress);
        $this->commandBus->dispatch($command);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
