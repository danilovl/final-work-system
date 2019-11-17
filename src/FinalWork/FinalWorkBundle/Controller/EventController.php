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

namespace FinalWork\FinalWorkBundle\Controller;

use FinalWork\FinalWorkBundle\Model\Comment\CommentModel;
use FinalWork\FinalWorkBundle\Model\Event\EventModel;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException,
    NonUniqueResultException
};
use FinalWork\FinalWorkBundle\Constant\{
    FlashTypeConstant,
    VoterSupportConstant
};
use FinalWork\FinalWorkBundle\Entity\{
    Event,
    EventParticipant
};
use FinalWork\FinalWorkBundle\Form\{
    EventForm,
    CommentForm
};
use LogicException;
use OutOfBoundsException;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\Exception\InvalidArgumentException;

class EventController extends BaseController
{
    /**
     * @param Request $request
     * @param Event $event
     * @return Response
     *
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function detailAction(
        Request $request,
        Event $event
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $event);

        $user = $this->getUser();
        $eventCommentExist = $this->get('final_work.facade.comment')
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
                'event' => $event,
            ])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventComment = $this->get('final_work.factory.comment')
                ->createFromModel($eventCommentModel, $eventCommentExist);

            $this->get('final_work.event_dispatcher.event')
                ->onEventComment($eventComment, $eventCommentExist !== null);

            $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.save.success');
        }

        $eventAddressSkype = $this->get('final_work.facade.event_address')
            ->getSkypeByOwner($event->getOwner());

        $this->get('final_work.seo_page')->setTitle($event->toString());

        return $this->render('@FinalWork/event/detail.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'deleteForm' => $deleteForm = $this->createDeleteForm($event, 'event_delete')->createView(),
            'switchToSkype' => $eventAddressSkype ? true : false
        ]);
    }

    /**
     * @param Request $request
     * @param Event $event
     * @return Response
     *
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     * @throws LogicException
     * @throws AccessDeniedException
     * @throws ORMException
     */
    public function editAction(
        Request $request,
        Event $event
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $event);

        $user = $this->getUser();
        $eventParticipantArray = $this->get('final_work.facade.event_participant')
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

                $eventParticipant = $event->getParticipant() ?: new EventParticipant;
                if ($eventParticipantForm) {
                    $eventParticipant->setWork($eventParticipantForm->getWork());
                    $eventParticipant->setUser($eventParticipantForm->getUser());
                    $eventParticipant->setEvent($event);
                    $eventModel->participant = $eventParticipant;
                } elseif ($eventParticipant->getId()) {
                    $this->removeEntity($eventParticipant);
                    $eventModel->participant = null;
                }

                $this->get('final_work.factory.event')
                    ->flushFromModel($eventModel, $event);

                $this->get('final_work.event_dispatcher.event')
                    ->onEventEdit($event);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.save.success');

                return $this->redirectToRoute('event_detail', [
                    'id' => $this->hashIdEncode($event->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.save.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.save.error');
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

        $this->get('final_work.seo_page')->setTitle($event->toString());

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/event/edit.html.twig'), [
            'event' => $event,
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Event $event
     * @return RedirectResponse
     *
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function switchToSkypeAction(Event $event): RedirectResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::SWITCH_TO_SKYPE, $event);

        $eventAddressSkype = $this->get('final_work.facade.event_address')
            ->getSkypeByOwner($event->getOwner());

        if ($eventAddressSkype !== null) {
            $event->setAddress($eventAddressSkype);

            $this->flushEntity();

            $this->get('final_work.event_dispatcher.event')
                ->onEventSwitchToSkype($event);

            $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.save.success');
        } else {
            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.save.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.save.error');
        }

        return $this->redirectToRoute('event_detail', [
            'id' => $this->hashIdEncode($event->getId())
        ]);
    }

    /**
     * @param Request $request
     * @param Event $event
     * @return RedirectResponse
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(
        Request $request,
        Event $event
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $event);

        $form = $this->createDeleteForm($event, 'event_delete')
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->removeEntity($event);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.delete.success');

                return $this->redirectToRoute('event_calendar_manage');
            }

            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.delete.error');

            return $this->redirectToRoute('event_detail', [
                'id' => $this->hashIdEncode($event->getId())
            ]);
        }

        return $this->redirectToRoute('event_calendar_manage');
    }
}
