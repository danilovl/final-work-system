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

namespace App\Domain\EventSchedule\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Infrastructure\Service\RequestService;
use App\Domain\EventSchedule\Command\DeleteEventSchedule\DeleteEventScheduleCommand;
use App\Domain\EventSchedule\Entity\EventSchedule;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class EventScheduleDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private MessageBusInterface $commandBus
    ) {}

    public function __invoke(EventSchedule $eventSchedule): JsonResponse
    {
        $command = DeleteEventScheduleCommand::create($eventSchedule);
        $this->commandBus->dispatch($command);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
