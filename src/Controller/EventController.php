<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Controller;

use App\Model\Comment\CommentModel;
use App\Model\Event\EventModel;
use App\Constant\{
    FlashTypeConstant,
    VoterSupportConstant
};
use App\Entity\{
    Event,
    EventParticipant
};
use App\Form\{
    EventForm,
    CommentForm
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class EventController extends BaseController
{
    public function detail(
        Request $request,
        Event $event
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $event);

        $user = $this->getUser();
        $eventCommentExist = $this->get('app.facade.comment')
            ->getCommentByOwnerEvent($user, $event);

        $eventCommentModel = new CommentModel;
        $eventCommentModel->owner = $user;
        $eventCommentModel->event = $event;

        if ($eventCommentExist !== null) {
            $eventCommentModel = CommentModel::fromComment($eventCommentExist);
        }

        $form = $this
            ->createForm(CommentForm::class, $eventCommentModel, [
                'user' => $user,
                'event' => $event
            ])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventComment = $this->get('app.factory.comment')
                ->createFromModel($eventCommentModel, $eventCommentExist);

            $this->get('app.event_dispatcher.event')
                ->onEventComment($eventComment, $eventCommentExist !== null);

            $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');
        }

        $eventAddressSkype = $this->get('app.facade.event_address')
            ->getSkypeByOwner($event->getOwner());

        $this->get('app.seo_page')->setTitle($event->toString());

        return $this->render('event/detail.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'deleteForm' => $deleteForm = $this->createDeleteForm($event, 'event_delete')->createView(),
            'switchToSkype' => $eventAddressSkype ? true : false
        ]);
    }

    public function edit(
        Request $request,
        Event $event
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $event);

        $origin = clone $event;
        $user = $this->getUser();
        $eventParticipantArray = $this->get('app.facade.event_participant')
            ->getEventParticipantsByUserEvent($user, $event);

        $eventModel = EventModel::fromEvent($event);
        $form = $this->createForm(EventForm::class, $eventModel, [
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
                    $this->removeEntity($eventParticipant);
                    $eventModel->participant = null;
                }

                $this->get('app.factory.event')
                    ->flushFromModel($eventModel, $event);

                $this->get('app.event_dispatcher.event')
                    ->onEventEdit($event);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');

                return $this->redirectToRoute('event_detail', [
                    'id' => $this->hashIdEncode($event->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.save.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.save.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->createForm(EventForm::class, $eventModel, [
                'action' => $this->generateUrl('event_edit_ajax', [
                    'id' => $this->hashIdEncode($event->getId())
                ]),
                'method' => Request::METHOD_POST,
                'addresses' => $user->getEventAddressOwner(),
                'participants' => $eventParticipantArray
            ]);
            $form->get('participant')->isRequired();
        }

        $this->get('app.seo_page')->setTitle($event->toString());

        return $this->render($this->ajaxOrNormalFolder($request, 'event/edit.html.twig'), [
            'event' => $event,
            'form' => $form->createView()
        ]);
    }

    public function switchToSkype(Event $event): RedirectResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::SWITCH_TO_SKYPE, $event);

        $eventAddressSkype = $this->get('app.facade.event_address')
            ->getSkypeByOwner($event->getOwner());

        if ($eventAddressSkype !== null) {
            $event->setAddress($eventAddressSkype);

            $this->flushEntity($event);

            $this->get('app.event_dispatcher.event')
                ->onEventSwitchToSkype($event);

            $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');
        } else {
            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.save.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.save.error');
        }

        return $this->redirectToRoute('event_detail', [
            'id' => $this->hashIdEncode($event->getId())
        ]);
    }

    public function delete(
        Request $request,
        Event $event
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $event);

        $form = $this->createDeleteForm($event, 'event_delete')
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->removeEntity($event);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.delete.success');

                return $this->redirectToRoute('event_calendar_manage');
            }

            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.delete.error');

            return $this->redirectToRoute('event_detail', [
                'id' => $this->hashIdEncode($event->getId())
            ]);
        }

        return $this->redirectToRoute('event_calendar_manage');
    }
}
