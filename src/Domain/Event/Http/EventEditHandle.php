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

use App\Application\Constant\FlashTypeConstant;
use App\Application\Service\{
    UserService,
    RequestService,
    SeoPageService,
    TwigRenderService,
    EntityManagerService
};
use App\Domain\Event\Entity\Event;
use App\Domain\Event\EventDispatcher\EventEventDispatcherService;
use App\Domain\Event\EventModel;
use App\Domain\Event\Facade\EventParticipantFacade;
use App\Domain\Event\Factory\EventFactory;
use App\Domain\Event\Form\EventForm;
use App\Domain\EventParticipant\Entity\EventParticipant;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\Routing\RouterInterface;

class EventEditHandle
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly UserService $userService,
        private readonly EntityManagerService $entityManagerService,
        private readonly TwigRenderService $twigRenderService,
        private readonly EventFactory $eventFactory,
        private readonly SeoPageService $seoPageService,
        private readonly FormFactoryInterface $formFactory,
        private readonly HashidsServiceInterface $hashidsService,
        private readonly EventParticipantFacade $eventParticipantFacade,
        private readonly EventEventDispatcherService $eventEventDispatcherService,
        private readonly RouterInterface $router
    ) {}

    public function handle(Request $request, Event $event): Response
    {
        $origin = clone $event;
        $user = $this->userService->getUser();
        $eventParticipantArray = $this->eventParticipantFacade
            ->getEventParticipantsByUserEvent($user, $event);

        $eventModel = EventModel::fromEvent($event);
        $form = $this->formFactory
            ->create(EventForm::class, $eventModel, [
                'addresses' => $user->getEventAddressOwner(),
                'participants' => $eventParticipantArray
            ]);
        $form->get('participant')->isRequired();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $eventParticipantForm = $eventModel->participant;

                $eventParticipant = $origin->getParticipant() ?: new EventParticipant;
                if ($eventParticipantForm) {
                    $eventParticipant->setWork($eventParticipantForm->getWork());
                    $eventParticipant->setUser($eventParticipantForm->getUser());
                    $eventParticipant->setEvent($event);
                    $eventModel->participant = $eventParticipant;
                } elseif ($eventParticipant->getId()) {
                    $this->entityManagerService->remove($eventParticipant);
                    $eventModel->participant = null;
                }

                $this->eventFactory->flushFromModel($eventModel, $event);
                $this->eventEventDispatcherService->onEventEdit($event);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');

                return $this->requestService->redirectToRoute('event_detail', [
                    'id' => $this->hashidsService->encode($event->getId())
                ]);
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.save.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.save.error');
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

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'event/edit.html.twig');

        return $this->twigRenderService->render($template, [
            'event' => $event,
            'form' => $form->createView()
        ]);
    }
}
