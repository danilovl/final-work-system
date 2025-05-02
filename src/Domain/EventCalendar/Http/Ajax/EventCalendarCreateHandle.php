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
use App\Application\Helper\{
    SortFunctionHelper,
    FormValidationMessageHelper
};
use App\Application\Service\{
    RequestService,
    EntityManagerService
};
use App\Domain\Event\EventDispatcher\EventEventDispatcher;
use App\Domain\Event\Facade\EventCalendarFacade;
use App\Domain\Event\Factory\EventFactory;
use App\Domain\Event\Form\EventForm;
use App\Domain\Event\Model\EventModel;
use App\Domain\EventParticipant\Entity\EventParticipant;
use App\Domain\User\Service\{
    UserService,
    UserWorkService
};
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\Entity\Work;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class EventCalendarCreateHandle
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private RequestService $requestService,
        private EventCalendarFacade $eventCalendarFacade,
        private UserService $userService,
        private UserWorkService $userWorkService,
        private EventFactory $eventFactory,
        private FormFactoryInterface $formFactory,
        private EventEventDispatcher $eventEventDispatcherService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->userService->getUser();
        /** @var WorkStatus $workStatus */
        $workStatus = $this->entityManagerService->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE->value);

        $userWorks = $this->userWorkService->getWorkBy(
            $user,
            WorkUserTypeConstant::SUPERVISOR->value,
            null,
            $workStatus
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

            $this->eventEventDispatcherService->onEventCalendarCreate($event);
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
