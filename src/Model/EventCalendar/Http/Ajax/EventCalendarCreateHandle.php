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

use App\Exception\AjaxRuntimeException;
use App\Model\Event\EventDispatcher\EventEventDispatcherService;
use App\Constant\{
    WorkStatusConstant,
    WorkUserTypeConstant,
    AjaxJsonTypeConstant
};
use App\Helper\FormValidationMessageHelper;
use App\Model\Event\Facade\EventCalendarFacade;
use App\Model\Event\Factory\EventFactory;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\{
    Work,
    WorkStatus,
    EventParticipant
};
use App\Model\Event\Form\EventForm;
use App\Helper\SortFunctionHelper;
use App\Model\Event\EventModel;
use App\Model\User\Service\UserWorkService;
use App\Service\{
    UserService,
    RequestService,
    EntityManagerService
};
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class EventCalendarCreateHandle
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private RequestService $requestService,
        private EventCalendarFacade $eventCalendarFacade,
        private UserService $userService,
        private UserWorkService $userWorkService,
        private EventFactory $eventFactory,
        private FormFactoryInterface $formFactory,
        private EventEventDispatcherService $eventEventDispatcherService
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $user = $this->userService->getUser();
        $userWorks = $this->userWorkService->getWorkBy(
            $user,
            WorkUserTypeConstant::SUPERVISOR,
            null,
            $this->entityManagerService->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE)
        );
        $eventParticipantArray = [];

        /** @var Work $work */
        foreach ($userWorks as $work) {
            $eventParticipant = new EventParticipant;
            $eventParticipant->setUser($work->getAuthor());
            $eventParticipant->setWork($work);
            $eventParticipantArray[] = $eventParticipant;
        }

        SortFunctionHelper::eventParticipantSort($eventParticipantArray);

        $eventModel = new EventModel;
        $eventModel->owner = $user;

        $form = $this->formFactory
            ->create(EventForm::class, $eventModel, [
                'addresses' => $user->getEventAddressOwner(),
                'participants' => $eventParticipantArray
            ])
            ->handleRequest($request);

        try {
            if ($form->isSubmitted() && !$form->isValid()) {
                throw new AjaxRuntimeException('Form not valid');
            }

            $event = $this->eventFactory->flushFromModel($eventModel);

            $eventParticipant = $eventModel->participant;
            if ($eventParticipant !== null) {
                $eventParticipant->setEvent($event);

                $this->entityManagerService->flush();
                $event->setParticipant($eventParticipant);
            }

            $this->eventEventDispatcherService
                ->onEventCalendarCreate($event, $eventParticipant !== null);
        } catch (AjaxRuntimeException) {
            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
                'data' => FormValidationMessageHelper::getErrorMessages($form)
            ]);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS, [
            'event' => $this->eventCalendarFacade->convertEventCreateToArray($event),
        ]);
    }
}
