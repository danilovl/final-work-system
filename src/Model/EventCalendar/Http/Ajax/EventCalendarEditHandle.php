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

namespace App\Model\EventCalendar\Http\Ajax;

use App\Constant\AjaxJsonTypeConstant;
use App\Model\Event\EventDispatcher\EventEventDispatcherService;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Event;
use App\Service\{
    RequestService,
    EntityManagerService
};
use Symfony\Component\HttpFoundation\JsonResponse;

class EventCalendarEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private EntityManagerService $entityManagerService,
        private HashidsServiceInterface $hashidsService,
        private EventEventDispatcherService $eventEventDispatcherService
    ) {
    }

    public function handle(Request $request, Event $event): JsonResponse
    {
        $event->setStart(new DateTime($request->request->get('start')));
        $event->setEnd(new DateTime($request->request->get('end')));

        $this->entityManagerService->flush($event);
        $this->eventEventDispatcherService->onEventCalendarEdit($event);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS, [
            'event_id' => $this->hashidsService->encode($event->getId())
        ]);
    }
}
