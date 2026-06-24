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
use App\Domain\EventCalendar\DTO\Api\Input\EventCalendarGetEventInput;
use App\Domain\Work\Entity\Work;
use App\Infrastructure\Service\AuthorizationCheckerService;
use App\Domain\EventCalendar\Http\Api\{
    EventCalendarGetEventHandle,
    EventCalendarUserReservationWorksHandle,
    EventCalendarUserReservationWorkHandle,
    EventCalendarManageCreateDataHandle
};
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;

readonly class EventCalendarController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private EventCalendarGetEventHandle $eventCalendarGetEventHandle,
        private EventCalendarUserReservationWorksHandle $eventCalendarUserReservationWorksHandle,
        private EventCalendarUserReservationWorkHandle $eventCalendarUserReservationWorkHandle,
        private EventCalendarManageCreateDataHandle $eventCalendarManageCreateDataHandle
    ) {}

    public function getEvent(
        #[MapQueryString] EventCalendarGetEventInput $input,
        string $type
    ): JsonResponse {
        return $this->eventCalendarGetEventHandle->__invoke($type, $input->start, $input->end);
    }

    public function getUserReservationWorks(): JsonResponse
    {
        return $this->eventCalendarUserReservationWorksHandle->__invoke();
    }

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
}
