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
    RequestService,
    EntityManagerService
};
use App\Domain\Event\Entity\Event;
use App\Domain\Event\EventDispatcher\EventEventDispatcherService;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use DateTime;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class EventCalendarEditHandle
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly EntityManagerService $entityManagerService,
        private readonly HashidsServiceInterface $hashidsService,
        private readonly EventEventDispatcherService $eventEventDispatcherService
    ) {}

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
