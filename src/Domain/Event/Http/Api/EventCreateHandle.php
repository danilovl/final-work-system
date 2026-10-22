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

namespace App\Domain\Event\Http\Api;

use App\Infrastructure\Service\EntityManagerService;
use App\Domain\Event\DTO\Api\EventDTO;
use App\Domain\Event\DTO\Api\Input\EventCreateInput;
use App\Domain\Event\EventDispatcher\EventEventDispatcher;
use App\Domain\Event\Factory\EventFactory;
use App\Domain\Event\Model\EventModel;
use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\EventType\Entity\EventType;
use App\Domain\User\Service\UserService;
use Danilovl\ObjectDtoMapper\Service\ObjectToDtoMapperInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class EventCreateHandle
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private UserService $userService,
        private EventFactory $eventFactory,
        private ObjectToDtoMapperInterface $objectToDtoMapper,
        private EventEventDispatcher $eventEventDispatcher
    ) {}

    public function __invoke(EventCreateInput $input): EventDTO
    {
        $user = $this->userService->getUser();

        /** @var EventType|null $eventType */
        $eventType = $this->entityManagerService->getRepository(EventType::class)->find($input->typeId);
        if ($eventType === null) {
            throw new NotFoundHttpException('Event type not found');
        }

        $eventAddress = null;
        if ($input->addressId !== null) {
            /** @var EventAddress|null $eventAddress */
            $eventAddress = $this->entityManagerService->getRepository(EventAddress::class)->find($input->addressId);
            if ($eventAddress === null) {
                throw new NotFoundHttpException('Event address not found');
            }
        }

        $eventModel = new EventModel;
        $eventModel->owner = $user;
        $eventModel->type = $eventType;
        $eventModel->name = $input->name;
        $eventModel->address = $eventAddress;
        $eventModel->start = $input->start;
        $eventModel->end = $input->end;

        $event = $this->eventFactory->flushFromModel($eventModel);

        $this->eventEventDispatcher->onEventCalendarCreate($event);

        /** @var EventDTO $eventDTO */
        $eventDTO = $this->objectToDtoMapper->map($event, EventDTO::class);

        return $eventDTO;
    }
}
