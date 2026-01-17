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

namespace App\Domain\EventCalendar\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Infrastructure\Service\RequestService;
use App\Domain\Event\Facade\EventCalendarFacade;
use App\Domain\EventCalendar\Request\GetEventRequest;
use App\Domain\User\Service\UserService;
use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class EventCalendarGetEventHandle
{
    public function __construct(
        private RequestService $requestService,
        private EventCalendarFacade $eventCalendarFacade,
        private UserService $userService
    ) {}

    public function __invoke(GetEventRequest $getEventRequest): JsonResponse
    {
        $request = $getEventRequest->getRequest();

        $startDate = new DateTime($getEventRequest->start);
        $endDate = new DateTime($getEventRequest->end);

        $events = $this->eventCalendarFacade->getEventsByOwner(
            $this->userService->getUser(),
            $request->attributes->getString('type'),
            $startDate,
            $endDate
        );

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS, [
            'events' => $events
        ]);
    }
}
