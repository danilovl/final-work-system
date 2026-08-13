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

namespace App\Domain\EventCalendar\Controller\Api;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\Event\Entity\Event;
use App\Domain\Work\DTO\Api\WorkDTO;
use App\Domain\EventCalendar\DTO\Api\Input\{
    EventCalendarGetEventInput,
    EventCalendarEventInput
};
use App\Domain\Work\Entity\Work;
use App\Infrastructure\Service\AuthorizationCheckerService;
use App\Domain\EventCalendar\Http\Api\{
    EventCalendarGetEventHandle,
    EventCalendarUserReservationWorksHandle,
    EventCalendarUserReservationWorkHandle,
    EventCalendarManageCreateDataHandle,
    EventCalendarManageCreateEventHandle
};
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\{
    MapQueryString,
    MapRequestPayload
};
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'EventCalendar')]
readonly class EventCalendarController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private EventCalendarGetEventHandle $eventCalendarGetEventHandle,
        private EventCalendarUserReservationWorksHandle $eventCalendarUserReservationWorksHandle,
        private EventCalendarUserReservationWorkHandle $eventCalendarUserReservationWorkHandle,
        private EventCalendarManageCreateDataHandle $eventCalendarManageCreateDataHandle,
        private EventCalendarManageCreateEventHandle $eventCalendarManageCreateEventHandle
    ) {}

    #[OA\Get(
        path: '/api/key/events/calendar/{type}',
        description: 'Retrieves calendar events for the specified type within the given date range.',
        summary: 'Calendar events by type and date range'
    )]
    #[OA\Parameter(
        name: 'type',
        description: 'Calendar type',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string', enum: ['manage', 'reservation', 'detail'])
    )]
    #[OA\Parameter(
        name: 'start',
        description: 'Start date (YYYY-MM-DD)',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', example: '2024-01-01')
    )]
    #[OA\Parameter(
        name: 'end',
        description: 'End date (YYYY-MM-DD)',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', example: '2024-01-31')
    )]
    #[OA\Response(
        response: 200,
        description: 'Calendar events list',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 123),
                    new OA\Property(property: 'title', type: 'string', example: 'Meeting with advisor'),
                    new OA\Property(property: 'color', type: 'string', example: '#ff0000'),
                    new OA\Property(property: 'start', type: 'string', example: '2024-01-10 09:00:00'),
                    new OA\Property(property: 'end', type: 'string', example: '2024-01-10 10:00:00'),
                    new OA\Property(property: 'hasParticipant', type: 'boolean', example: true)
                ],
                type: 'object'
            )
        )
    )]
    public function getEvent(
        #[MapQueryString] EventCalendarGetEventInput $input,
        string $type
    ): JsonResponse {
        return $this->eventCalendarGetEventHandle->__invoke($type, $input->start, $input->end);
    }

    #[OA\Get(
        path: '/api/key/events/calendar/reservation/user/works',
        description: 'Retrieves active works of the current user available for event reservation.',
        summary: 'User reservation works'
    )]
    #[OA\Response(
        response: 200,
        description: 'List of user works available for reservation',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: WorkDTO::class))
        )
    )]
    public function getUserReservationWorks(): JsonResponse
    {
        return $this->eventCalendarUserReservationWorksHandle->__invoke();
    }

    #[OA\Post(
        path: '/api/key/events/calendar/reservation/events/{id_event}/works/{id_work}',
        description: 'Reserves a work of the event owner for the current user in the specified event.',
        summary: 'Reserve work for event'
    )]
    #[OA\Parameter(
        name: 'id_event',
        description: 'Event ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 123)
    )]
    #[OA\Parameter(
        name: 'id_work',
        description: 'Work ID (must belong to the event owner as supervisor work)',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 456)
    )]
    #[OA\Response(
        response: 204,
        description: 'Reservation successful (no content)'
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid reservation: the selected work cannot be reserved for this event'
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied'
    )]
    #[OA\Response(
        response: 404,
        description: 'Event or Work not found'
    )]
    public function postUserReservationWork(
        #[MapEntity(mapping: ['id_event' => 'id'])] Event $event,
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work
    ): JsonResponse {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::RESERVATION->value, $event);

        return $this->eventCalendarUserReservationWorkHandle->__invoke($event, $work);
    }

    public function getManageCreateData(): JsonResponse
    {
        return $this->eventCalendarManageCreateDataHandle->__invoke();
    }

    public function postEventCreate(
        #[MapRequestPayload] EventCalendarEventInput $input
    ): JsonResponse {
        return $this->eventCalendarManageCreateEventHandle->__invoke($input);
    }
}
