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

use App\Infrastructure\Constant\AjaxJsonTypeConstant;
use App\Infrastructure\Service\RequestService;
use App\Domain\EventCalendar\Facade\EventCalendarFacade;
use App\Domain\EventCalendar\Request\GetEventRequest;
use App\Domain\User\Service\UserService;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class EventCalendarGetEventHandle
{
    public function __construct(
        private RequestService $requestService,
        private EventCalendarFacade $eventCalendarFacade,
        private UserService $userService
    ) {}

    public function __invoke(GetEventRequest $getEventRequest, string $type): JsonResponse
    {
        $startDate = new DateTimeImmutable($getEventRequest->start)->setTime(23, 59, 59);
        $endDate = new DateTimeImmutable($getEventRequest->end)->setTime(23, 59, 59);

        $events = $this->eventCalendarFacade->listEventsByOwner(
            $this->userService->getUser(),
            $type,
            $startDate,
            $endDate
        );

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS, [
            'events' => $events
        ]);
    }
}
