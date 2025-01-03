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
    EntityManagerService,
    RequestService,
    SeoPageService,
    TwigRenderService
};
use App\Domain\Event\Entity\Event;
use App\Domain\Event\EventDispatcher\EventEventDispatcher;
use App\Domain\Event\Facade\EventParticipantFacade;
use App\Domain\Event\Factory\EventFactory;
use App\Domain\Event\Form\EventForm;
use App\Domain\Event\Model\EventModel;
use App\Domain\EventParticipant\Entity\EventParticipant;
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
        private EventFactory $eventFactory,
        private SeoPageService $seoPageService,
        private FormFactoryInterface $formFactory,
        private HashidsServiceInterface $hashidsService,
        private EventParticipantFacade $eventParticipantFacade,
        private EventEventDispatcher $eventEventDispatcherService,
        private RouterInterface $router
    ) {}

    public function __invoke(Request $request, Event $event): Response
    {
        $origin = clone $event;
        $user = $this->userService->getUser();
        $eventParticipantArray = $this->eventParticipantFacade
            ->getEventParticipantsByUserEvent($user, $origin);

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

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.save.success');

                return $this->requestService->redirectToRoute('event_detail', [
                    'id' => $this->hashidsService->encode($event->getId())
                ]);
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.save.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.save.error');
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
