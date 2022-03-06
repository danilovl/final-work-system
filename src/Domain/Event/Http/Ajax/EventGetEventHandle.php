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

namespace App\Domain\Event\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Service\{
    RequestService,
    UserService
};
use App\Domain\Event\DataTransferObject\EventRepositoryData;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Facade\{
    EventCalendarFacade,
    EventFacade
};
use DateTime;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

class EventGetEventHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EventFacade $eventFacade,
        private EventCalendarFacade $eventCalendarFacade
    ) {
    }

    public function handle(Request $request, Event $event): JsonResponse
    {
        $user = $this->userService->getUser();

        $mediaData = EventRepositoryData::createFromArray([
            'user' => $user,
            'startDate' => new DateTime($request->request->get('start')),
            'endDate' => new DateTime($request->request->get('end'))
        ]);

        if ($event->isOwner($user)) {
            $userEvents = $this->eventFacade->getEventsByOwner($mediaData);
        } else {
            $userEvents = $this->eventFacade->getEventsByParticipant($mediaData);
        }

        $events = $this->eventCalendarFacade
            ->convertUserEventsToArray($event, $userEvents);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS, [
            'events' => json_encode($events, JSON_PRETTY_PRINT)
        ]);
    }
}
