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

namespace App\Model\Event\Http\Ajax;

use App\Constant\AjaxJsonTypeConstant;
use App\Helper\FormValidationMessageHelper;
use App\Entity\{
    Event,
    EventParticipant
};
use App\EventDispatcher\EventEventDispatcherService;
use App\Form\EventForm;
use App\Model\Event\EventModel;
use App\Model\Event\Facade\EventParticipantFacade;
use App\Model\Event\Factory\EventFactory;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\{
    UserService,
    RequestService,
    EntityManagerService
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class EventEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EntityManagerService $entityManagerService,
        private EventFactory $eventFactory,
        private FormFactoryInterface $formFactory,
        private EventParticipantFacade $eventParticipantFacade,
        private EventEventDispatcherService $eventEventDispatcherService
    ) {
    }

    public function handle(Request $request, Event $event): JsonResponse
    {
        $origin = clone $event;
        $user = $this->userService->getUser();
        $eventModel = EventModel::fromEvent($event);

        $form = $this->formFactory
            ->create(EventForm::class, $eventModel, [
                'addresses' => $user->getEventAddressOwner(),
                'participants' => $this->eventParticipantFacade
                    ->getEventParticipantsByUserEvent($user, $event)
            ]);
        $form->get('participant')->isRequired();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventParticipantForm = $eventModel->participant;

            $eventParticipant = $origin->getParticipant() ?? new EventParticipant;
            if ($eventParticipantForm !== null) {
                $eventParticipant->setWork($eventParticipantForm->getWork());
                $eventParticipant->setUser($eventParticipantForm->getUser());
                $eventParticipant->setEvent($event);
                $eventModel->participant = $eventParticipant;
            } elseif ($eventParticipant->getId() !== null) {
                $this->entityManagerService->remove($eventParticipant);
                $eventModel->participant = null;
            }

            $this->eventFactory->flushFromModel($eventModel, $event);
            $this->eventEventDispatcherService->onEventEdit($event);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
