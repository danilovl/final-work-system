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
use App\Application\Service\{
    UserService,
    RequestService
};
use App\Domain\Event\Facade\EventCalendarFacade;
use DateTime;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class EventCalendarGetEventHandle
{
    public function __construct(
        private RequestService $requestService,
        private EventCalendarFacade $eventCalendarFacade,
        private UserService $userService
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $startDate = new DateTime($request->request->get('start'));
        $endDate = new DateTime($request->request->get('end'));

        $events = $this->eventCalendarFacade->getEventsByOwner(
            $this->userService->getUser(),
            $request->attributes->get('type'),
            $startDate,
            $endDate
        );

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS, [
            'events' => json_encode($events, JSON_PRETTY_PRINT),
        ]);
    }
}