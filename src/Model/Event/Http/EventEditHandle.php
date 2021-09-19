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

namespace App\Model\Event\Http;

use App\Entity\{
    Event,
    EventParticipant
};
use App\EventDispatcher\EventEventDispatcherService;
use App\Form\EventForm;
use App\Model\Event\EventModel;
use App\Model\Event\Facade\EventParticipantFacade;
use App\Model\Event\Factory\EventFactory;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use App\Constant\FlashTypeConstant;
use App\Service\{
    UserService,
    RequestService,
    SeoPageService,
    TwigRenderService,
    EntityManagerService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\Routing\RouterInterface;

class EventEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EntityManagerService $entityManagerService,
        private TwigRenderService $twigRenderService,
        private EventFactory $eventFactory,
        private SeoPageService $seoPageService,
        private FormFactoryInterface $formFactory,
        private HashidsServiceInterface $hashidsService,
        private EventParticipantFacade $eventParticipantFacade,
        private EventEventDispatcherService $eventEventDispatcherService,
        private RouterInterface $router
    ) {
    }

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
