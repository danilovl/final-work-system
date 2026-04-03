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
use App\Domain\Event\DTO\Repository\EventRepositoryDTO;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Facade\{
    EventFacade,
    EventCalendarFacade
};
use App\Domain\User\Service\UserService;
use App\Infrastructure\Service\RequestService;
use DateTime;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class EventGetEventHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EventFacade $eventFacade,
        private EventCalendarFacade $eventCalendarFacade
    ) {}

    public function __invoke(Request $request, Event $event): JsonResponse
    {
        $user = $this->userService->getUser();

        $startDate = new DateTime($request->request->getString('start'));
        $endDate = new DateTime($request->request->getString('end'));

        $mediaData = new EventRepositoryDTO(
            user: $user,
            startDate: $startDate,
            endDate: $endDate
        );

        if ($event->isOwner($user)) {
            $userEvents = $this->eventFacade->listEventsByOwner($mediaData);
        } else {
            $userEvents = $this->eventFacade->listByParticipant($mediaData);
        }

        $events = $this->eventCalendarFacade
            ->convertUserEventsToArray($event, $userEvents);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS, [
            'events' => $events
        ]);
    }
}
