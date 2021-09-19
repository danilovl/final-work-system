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
use App\EventDispatcher\EventEventDispatcherService;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Event;
use App\Service\{
    RequestService,
    EntityManagerService
};
use Symfony\Component\HttpFoundation\Response;

class EventCalendarEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private EntityManagerService $entityManagerService,
        private HashidsServiceInterface $hashidsService,
        private EventEventDispatcherService $eventEventDispatcherService
    ) {
    }

    public function handle(Request $request, Event $event): Response
    {
        $event->setStart(new DateTime($request->get('start')));
        $event->setEnd(new DateTime($request->get('end')));

        $this->entityManagerService->flush($event);
        $this->eventEventDispatcherService->onEventCalendarEdit($event);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS, [
            'event_id' => $this->hashidsService->encode($event->getId())
        ]);
    }
}
