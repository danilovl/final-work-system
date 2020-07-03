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

use App\Model\EventSchedule\{
    EventScheduleModel,
    EventScheduleCloneModel
};
use App\Constant\{
    FlashTypeConstant,
    VoterSupportConstant
};
use App\Entity\EventSchedule;
use App\Form\{
    EventScheduleForm,
    EventScheduleCloneForm
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class EventScheduleController extends BaseController
{
    public function create(Request $request): Response
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
                $this->get('app.factory.event_schedule')
                    ->flushFromModel($eventScheduleModel);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->redirectToRoute('event_schedule_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        $this->get('app.seo_page')->setTitle('app.page.event_schedule_create');

        return $this->render('event_schedule/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function list(Request $request): Response
    {
        $eventSchedulesQuery = $this->get('app.facade.event_schedule')
            ->queryEventSchedulesByOwner($this->getUser());

        $this->get('app.seo_page')->setTitle('app.page.event_schedule_list');

        return $this->render('event_schedule/list.html.twig', [
            'eventSchedules' => $this->createPagination($request, $eventSchedulesQuery)
        ]);
    }

    public function detail(EventSchedule $eventSchedule): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $eventSchedule);

        $this->get('app.seo_page')->setTitle($eventSchedule->getName());

        return $this->render('event_schedule/detail.html.twig', [
            'eventSchedule' => $eventSchedule,
            'weekDay' => $this->get('app.date')->getWeekDaysArray(),
            'deleteForm' => $this->createDeleteForm($eventSchedule, 'event_schedule_delete')->createView()
        ]);
    }

    public function edit(
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
                $this->get('app.factory.event_schedule')
                    ->flushFromModel($eventScheduleModel, $eventSchedule);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, $this->trans('app.flash.form.save.success'));

                return $this->redirectToRoute('event_schedule_detail', [
                    'id' => $this->hashIdEncode($eventSchedule->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.save.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.save.error');
        }

        $this->get('app.seo_page')->setTitle('app.page.event_schedule_create');

        return $this->render('event_schedule/edit.html.twig', [
            'eventSchedule' => $eventSchedule,
            'form' => $form->createView()
        ]);
    }

    public function clone(
        Request $request,
        EventSchedule $eventSchedule
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::CLONE, $eventSchedule);

        $eventScheduleCloneModel = new EventScheduleCloneModel;
        $form = $this->createForm(EventScheduleCloneForm::class, $eventScheduleCloneModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('app.factory.event_schedule')
                    ->cloneEventSchedule($this->getUser(), $eventSchedule, $eventScheduleCloneModel->start);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->redirectToRoute('event_schedule_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->createForm(EventScheduleCloneForm::class, null, [
                'action' => $this->generateUrl('event_schedule_clone_ajax', [
                    'id' => $this->hashIdEncode($eventSchedule->getId())
                ]),
                'method' => Request::METHOD_POST
            ]);
        }

        $this->get('app.seo_page')->setTitle($eventSchedule->getName());

        return $this->render($this->ajaxOrNormalFolder($request, 'event_schedule/clone.html.twig'), [
            'eventSchedule' => $eventSchedule,
            'form' => $form->createView()
        ]);
    }

    public function delete(
        Request $request,
        EventSchedule $eventSchedule
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $eventSchedule);

        $form = $this->createDeleteForm($eventSchedule, 'event_schedule_delete')
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->removeEntity($eventSchedule);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.delete.success');

                return $this->redirectToRoute('event_schedule_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.delete.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.delete.error');
        }

        return $this->redirectToRoute('event_schedule_list');
    }
}