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

namespace App\Domain\Event\Bus\Command\CreateEvent;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Factory\EventFactory;
use App\Infrastructure\Service\EntityManagerService;

readonly class CreateEventHandler implements CommandHandlerInterface
{
    public function __construct(
        private EventFactory $eventFactory,
        private EntityManagerService $entityManagerService
    ) {}

    public function __invoke(CreateEventCommand $command): Event
    {
        $eventModel = $command->eventModel;
        $event = $this->eventFactory->flushFromModel($eventModel);

        $eventParticipant = $eventModel->participant;
        if ($eventParticipant !== null) {
            $eventParticipant->setEvent($event);

            $this->entityManagerService->flush();
            $event->setParticipant($eventParticipant);
        }

        return $event;
    }
}
