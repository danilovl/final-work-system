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
use App\Application\Exception\AjaxRuntimeException;
use App\Application\Helper\FormValidationMessageHelper;
use App\Domain\EventParticipant\Helper\SortFunctionHelper;
use App\Infrastructure\Service\{
    RequestService,
    EntityManagerService
};
use App\Domain\Event\EventDispatcher\EventEventDispatcher;
use App\Domain\EventCalendar\Facade\EventCalendarFacade;
use App\Infrastructure\Interfaces\Bus\{CommandBusInterface, QueryBusInterface};
use App\Domain\Event\Bus\Command\CreateEvent\CreateEventCommand;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Form\EventForm;
use App\Domain\Event\Model\EventModel;
use App\Domain\EventParticipant\Entity\EventParticipant;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\Entity\Work;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use App\Domain\Work\Bus\Query\UserWorks\UserWorksQueryTrait;

readonly class EventCalendarCreateHandle
{
    use UserWorksQueryTrait;

    public function __construct(
        private EntityManagerService $entityManagerService,
        private RequestService $requestService,
        private EventCalendarFacade $eventCalendarFacade,
        private UserService $userService,
        private QueryBusInterface $queryBus,
        private CommandBusInterface $commandBus,
        private FormFactoryInterface $formFactory,
        private EventEventDispatcher $eventEventDispatcherService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->userService->getUser();
        /** @var WorkStatus $workStatus */
        $workStatus = $this->entityManagerService->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE->value);

        $userWorks = $this->getUserWorks(
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

            $createEventCommand = CreateEventCommand::create($eventModel);
            /** @var Event $event */
            $event = $this->commandBus->dispatchResult($createEventCommand);

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
