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

namespace App\Domain\Event\Http;

use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Infrastructure\Service\{
    EntityManagerService,
    RequestService,
    SeoPageService,
    TwigRenderService
};
use App\Domain\Event\Bus\Command\EditEvent\EditEventCommand;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Facade\EventParticipantFacade;
use App\Domain\Event\Form\EventForm;
use App\Domain\Event\Model\EventModel;
use App\Domain\User\Service\UserService;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\Routing\RouterInterface;

readonly class EventEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EntityManagerService $entityManagerService,
        private TwigRenderService $twigRenderService,
        private SeoPageService $seoPageService,
        private FormFactoryInterface $formFactory,
        private HashidsServiceInterface $hashidsService,
        private EventParticipantFacade $eventParticipantFacade,
        private RouterInterface $router,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request, Event $event): Response
    {
        $user = $this->userService->getUser();
        $eventParticipantArray = $this->eventParticipantFacade
            ->getEventParticipantsByUserEvent($user, $event);

        $eventModel = EventModel::fromEvent($event);
        $eventModel->setActualParticipant($eventParticipantArray);

        $form = $this->formFactory
            ->create(EventForm::class, $eventModel, [
                'addresses' => $user->getEventAddressOwner(),
                'participants' => $eventParticipantArray,
                'isParticipantRequired' => true
            ])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($event->getParticipant() !== null) {
                $this->entityManagerService->remove($event->getParticipant());
            }

            $command = EditEventCommand::create($eventModel, $event);
            $this->commandBus->dispatch($command);

            return $this->requestService->redirectToRoute('event_detail', [
                'id' => $this->hashidsService->encode($event->getId())
            ]);
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->formFactory->create(EventForm::class, $eventModel, [
                'action' => $this->router->generate('event_edit_ajax', [
                    'id' => $this->hashidsService->encode($event->getId())
                ]),
                'method' => Request::METHOD_POST,
                'addresses' => $user->getEventAddressOwner(),
                'participants' => $eventParticipantArray
            ]);
            $form->get('participant')->isRequired();
        }

        $this->seoPageService->setTitle($event->toString());

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/event/edit.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'event' => $event,
            'form' => $form->createView()
        ]);
    }
}
