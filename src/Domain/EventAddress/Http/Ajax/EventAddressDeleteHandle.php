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

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Service\RequestService;
use App\Domain\EventAddress\Bus\Command\DeleteEventAddress\DeleteEventAddressCommand;
use App\Domain\EventAddress\Entity\EventAddress;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class EventAddressDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private MessageBusInterface $messageBus
    ) {}

    public function __invoke(EventAddress $eventAddress): JsonResponse
    {
        $command = DeleteEventAddressCommand::create($eventAddress);
        $this->messageBus->dispatch($command);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
