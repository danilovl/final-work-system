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

namespace FinalWork\FinalWorkBundle\Controller\Ajax;

use FinalWork\FinalWorkBundle\Constant\{
    AjaxJsonTypeConstant,
    VoterSupportConstant
};
use FinalWork\FinalWorkBundle\Model\EventSchedule\EventScheduleCloneModel;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};
use FinalWork\FinalWorkBundle\Entity\EventSchedule;
use FinalWork\FinalWorkBundle\Form\EventScheduleCloneForm;
use FinalWork\FinalWorkBundle\Helper\FormValidationMessageHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use FinalWork\FinalWorkBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class EventScheduleController extends BaseController
{
    /**
     * @param Request $request
     * @param EventSchedule $eventSchedule
     * @return JsonResponse
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function cloneAction(
        Request $request,
        EventSchedule $eventSchedule
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::CLONE, $eventSchedule);

        $eventScheduleCloneModel = new EventScheduleCloneModel;
        $form = $this->createForm(EventScheduleCloneForm::class, $eventScheduleCloneModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('final_work.factory.event_schedule')
                ->cloneEventSchedule($this->getUser(), $eventSchedule, $eventScheduleCloneModel->start);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param EventSchedule $eventSchedule
     * @return JsonResponse
     */
    public function deleteAction(EventSchedule $eventSchedule): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $eventSchedule);

        $this->removeEntity($eventSchedule);

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}