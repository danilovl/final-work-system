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

namespace App\Model\Event\Http\Ajax;

use App\Constant\AjaxJsonTypeConstant;
use App\DataTransferObject\Repository\EventData;
use App\Model\Event\Entity\Event;
use App\Model\Event\Facade\EventCalendarFacade;
use App\Model\Event\Facade\EventFacade;
use DateTime;
use App\Service\{
    UserService,
    RequestService
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
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

        $mediaData = EventData::createFromArray([
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
