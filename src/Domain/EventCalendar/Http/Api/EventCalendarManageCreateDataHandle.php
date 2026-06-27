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

namespace App\Domain\EventCalendar\Http\Api;

use App\Application\Mapper\ObjectToDtoMapper;
use App\Domain\EventAddress\DTO\Api\EventAddressDTO;
use App\Domain\EventCalendar\DTO\Api\Output\EventCalendarManageCreateDataOutput;
use App\Domain\EventParticipant\DTO\Api\EventParticipantDTO;
use App\Domain\EventType\DTO\Api\EventTypeDTO;
use App\Domain\EventType\Facade\EventTypeFacade;
use App\Domain\EventParticipant\Entity\EventParticipant;
use App\Domain\EventParticipant\Helper\SortFunctionHelper;
use App\Domain\User\Service\{
    UserService,
    UserWorkService
};
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\Entity\Work;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Infrastructure\Service\EntityManagerService;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class EventCalendarManageCreateDataHandle
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private UserService $userService,
        private UserWorkService $userWorkService,
        private ObjectToDtoMapper $objectToDtoMapper,
        private EventTypeFacade $eventTypeFacade
    ) {}

    public function __invoke(): JsonResponse
    {
        $user = $this->userService->getUser();
        
        /** @var WorkStatus $workStatus */
        $workStatus = $this->entityManagerService->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE->value);

        $userWorks = $this->userWorkService->getWorkBy(
            $user,
            WorkUserTypeConstant::SUPERVISOR->value,
            null,
            $workStatus
        );
        
        $eventParticipantArray = [];

        /** @var Work $work */
        foreach ($userWorks as $work) {
            $eventParticipant = new EventParticipant;
            $eventParticipant->setUser($work->getAuthor());
            $eventParticipant->setWork($work);
            $eventParticipantArray[] = $eventParticipant;
        }
        SortFunctionHelper::eventParticipantSort($eventParticipantArray);

        $participants = [];
        foreach ($eventParticipantArray as $eventParticipant) {
            $participants[] = $this->objectToDtoMapper->map($eventParticipant, EventParticipantDTO::class);
        }

        $addresses = [];
        foreach ($user->getEventAddressOwner() as $address) {
            $addresses[] = $this->objectToDtoMapper->map($address, EventAddressDTO::class);
        }

        $eventTypes = $this->eventTypeFacade->findAll();
        $types = [];
        foreach ($eventTypes as $eventType) {
            $types[] = $this->objectToDtoMapper->map($eventType, EventTypeDTO::class);
        }

        $output = new EventCalendarManageCreateDataOutput(
            types: $types,
            addresses: $addresses,
            participants: $participants
        );

        return new JsonResponse($output);
    }
}
