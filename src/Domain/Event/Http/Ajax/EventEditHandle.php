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

namespace App\Domain\Event\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Helper\FormValidationMessageHelper;
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Application\Service\{
    EntityManagerService,
    RequestService
};
use App\Domain\Event\Bus\Command\EditEvent\EditEventCommand;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Facade\EventParticipantFacade;
use App\Domain\Event\Form\EventForm;
use App\Domain\Event\Model\EventModel;
use App\Domain\EventParticipant\Entity\EventParticipant;
use App\Domain\User\Service\UserService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class EventEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EntityManagerService $entityManagerService,
        private FormFactoryInterface $formFactory,
        private EventParticipantFacade $eventParticipantFacade,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request, Event $event): JsonResponse
    {
        $origin = clone $event;
        $user = $this->userService->getUser();
        $eventModel = EventModel::fromEvent($event);

        $form = $this->formFactory
            ->create(EventForm::class, $eventModel, [
                'addresses' => $user->getEventAddressOwner(),
                'participants' => $this->eventParticipantFacade->getEventParticipantsByUserEvent($user, $origin)
            ]);

        $form->get('participant')->isRequired();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventParticipantForm = $eventModel->participant;
            $originParticipant = $origin->getParticipant();

            $eventParticipant = $originParticipant ?? new EventParticipant;
            if ($eventParticipantForm !== null) {
                $eventParticipant->setWork($eventParticipantForm->getWork());
                $eventParticipant->setUser($eventParticipantForm->getUser());
                $eventParticipant->setEvent($event);
                $eventModel->participant = $eventParticipant;
            } elseif ($originParticipant !== null) {
                $this->entityManagerService->remove($originParticipant);
                $eventModel->participant = null;
            }

            $command = EditEventCommand::create($eventModel, $event);
            $this->commandBus->dispatch($command);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
