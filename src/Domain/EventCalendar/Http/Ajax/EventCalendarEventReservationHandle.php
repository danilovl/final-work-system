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
use App\Application\Exception\AjaxRuntimeException;
use App\Application\Service\{
    EntityManagerService,
    RequestService
};
use App\Domain\Event\Entity\Event;
use App\Domain\Event\EventDispatcher\EventEventDispatcher;
use App\Domain\Event\Facade\EventCalendarFacade;
use App\Domain\EventCalendar\Form\EventWorkReservationForm;
use App\Domain\EventParticipant\Entity\EventParticipant;
use App\Domain\EventWorkReservation\Model\EventWorkReservationModel;
use App\Domain\User\Service\{
    UserService,
    UserWorkService
};
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class EventCalendarEventReservationHandle
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private RequestService $requestService,
        private EventCalendarFacade $eventCalendarFacade,
        private UserService $userService,
        private UserWorkService $userWorkService,
        private FormFactoryInterface $formFactory,
        private EventEventDispatcher $eventEventDispatcherService
    ) {}

    public function __invoke(Request $request, Event $event): JsonResponse
    {
        $user = $this->userService->getUser();

        /** @var WorkStatus $workStatus */
        $workStatus = $this->entityManagerService->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE->value);

        $userWorks = $this->userWorkService->getWorkBy(
            $user,
            WorkUserTypeConstant::AUTHOR->value,
            null,
            $workStatus
        );

        $eventWorkReservationModel = new EventWorkReservationModel;
        $form = $this->formFactory
            ->create(EventWorkReservationForm::class, $eventWorkReservationModel, [
                'works' => $userWorks
            ])
            ->handleRequest($request);

        try {
            if ($form->isSubmitted() && !$form->isValid()) {
                throw new AjaxRuntimeException('Form not valid');
            }

            $work = $eventWorkReservationModel->work;
            $ownerWorks = $event->getOwner()->getSupervisorWorks();

            if ($ownerWorks->contains($work) === false) {
                throw new AjaxRuntimeException('Work');
            }

            $appointmentParticipant = new EventParticipant;
            $appointmentParticipant->setEvent($event);
            $appointmentParticipant->setUser($user);
            $appointmentParticipant->setWork($work);

            $this->entityManagerService->persistAndFlush($appointmentParticipant);
            $event->setParticipant($appointmentParticipant);

            $this->eventEventDispatcherService
                ->onEventCalendarReservation($event);
        } catch (AjaxRuntimeException) {
            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE);
        }

        $eventCalendar = $this->eventCalendarFacade
            ->convertEventReservationToArray($event);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS, [
            'event' => $eventCalendar,
        ]);
    }
}
