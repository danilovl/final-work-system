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

use FinalWork\FinalWorkBundle\Model\EventSchedule\EventScheduleCloneModel;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};
use Exception;
use FinalWork\FinalWorkBundle\Model\EventSchedule\EventScheduleModel;
use FinalWork\FinalWorkBundle\Constant\{
    FlashTypeConstant,
    VoterSupportConstant
};
use FinalWork\FinalWorkBundle\Entity\EventSchedule;
use FinalWork\FinalWorkBundle\Form\{
    EventScheduleForm,
    EventScheduleCloneForm
};
use LogicException;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\Exception\InvalidArgumentException;

class EventScheduleController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(Request $request): Response
    {
        $user = $this->getUser();

        $eventScheduleModel = new EventScheduleModel;
        $eventScheduleModel->owner = $user;

        $form = $this
            ->createForm(EventScheduleForm::class, $eventScheduleModel, [
                'addresses' => $user->getEventAddressOwner()
            ])
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('final_work.factory.event_schedule')
                    ->flushFromModel($eventScheduleModel);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.create.success');

                return $this->redirectToRoute('event_schedule_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.create.error');
        }

        $this->get('final_work.seo_page')->setTitle('finalwork.page.event_schedule_create');

        return $this->render('@FinalWork/event_schedule/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @throws LogicException
     */
    public function listAction(Request $request): Response
    {
        $eventSchedulesQuery = $this->get('final_work.facade.event_schedule')
            ->queryEventSchedulesByOwner($this->getUser());

        $this->get('final_work.seo_page')->setTitle('finalwork.page.event_schedule_list');

        return $this->render('@FinalWork/event_schedule/list.html.twig', [
            'eventSchedules' => $this->createPagination($request, $eventSchedulesQuery)
        ]);
    }

    /**
     * @param EventSchedule $eventSchedule
     * @return Response
     *
     * @throws InvalidArgumentException
     * @throws AccessDeniedException
     * @throws LogicException
     */
    public function detailAction(EventSchedule $eventSchedule): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $eventSchedule);

        $this->get('final_work.seo_page')->setTitle($eventSchedule->getName());

        return $this->render('@FinalWork/event_schedule/detail.html.twig', [
            'eventSchedule' => $eventSchedule,
            'weekDay' => $this->get('final_work.date')->getWeekDaysArray(),
            'deleteForm' => $this->createDeleteForm($eventSchedule, 'event_schedule_delete')->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param EventSchedule $eventSchedule
     * @return Response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(
        Request $request,
        EventSchedule $eventSchedule
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $eventSchedule);

        $eventScheduleModel = EventScheduleModel::fromEventSchedule($eventSchedule);
        $form = $this
            ->createForm(EventScheduleForm::class, $eventScheduleModel, [
                'addresses' => $this->getUser()->getEventAddressOwner()
            ])
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('final_work.factory.event_schedule')
                    ->flushFromModel($eventScheduleModel, $eventSchedule);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, $this->trans('finalwork.flash.form.save.success'));

                return $this->redirectToRoute('event_schedule_detail', [
                    'id' => $this->hashIdEncode($eventSchedule->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.save.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.save.error');
        }

        $this->get('final_work.seo_page')->setTitle('finalwork.page.event_schedule_create');

        return $this->render('@FinalWork/event_schedule/edit.html.twig', [
            'eventSchedule' => $eventSchedule,
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param EventSchedule $eventSchedule
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function cloneAction(
        Request $request,
        EventSchedule $eventSchedule
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::CLONE, $eventSchedule);

        $eventScheduleCloneModel = new EventScheduleCloneModel;
        $form = $this->createForm(EventScheduleCloneForm::class, $eventScheduleCloneModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('final_work.factory.event_schedule')
                    ->cloneEventSchedule($this->getUser(), $eventSchedule, $eventScheduleCloneModel->start);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.create.success');

                return $this->redirectToRoute('event_schedule_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->createForm(EventScheduleCloneForm::class, null, [
                'action' => $this->generateUrl('event_schedule_clone_ajax', [
                    'id' => $this->hashIdEncode($eventSchedule->getId())
                ]),
                'method' => Request::METHOD_POST
            ]);
        }

        $this->get('final_work.seo_page')->setTitle($eventSchedule->getName());

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/event_schedule/clone.html.twig'), [
            'eventSchedule' => $eventSchedule,
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param EventSchedule $eventSchedule
     * @return RedirectResponse
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(
        Request $request,
        EventSchedule $eventSchedule
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $eventSchedule);

        $form = $this->createDeleteForm($eventSchedule, 'event_schedule_delete')
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->removeEntity($eventSchedule);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.delete.success');

                return $this->redirectToRoute('event_schedule_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.delete.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.delete.error');
        }

        return $this->redirectToRoute('event_schedule_list');
    }
}