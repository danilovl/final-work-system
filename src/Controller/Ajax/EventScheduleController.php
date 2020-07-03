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

namespace App\Controller\Ajax;

use App\Constant\{
    AjaxJsonTypeConstant,
    VoterSupportConstant
};
use App\Model\EventSchedule\EventScheduleCloneModel;
use App\Entity\EventSchedule;
use App\Form\EventScheduleCloneForm;
use App\Helper\FormValidationMessageHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class EventScheduleController extends BaseController
{
    public function clone(
        Request $request,
        EventSchedule $eventSchedule
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::CLONE, $eventSchedule);

        $eventScheduleCloneModel = new EventScheduleCloneModel;
        $form = $this->createForm(EventScheduleCloneForm::class, $eventScheduleCloneModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.factory.event_schedule')
                ->cloneEventSchedule($this->getUser(), $eventSchedule, $eventScheduleCloneModel->start);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    public function delete(EventSchedule $eventSchedule): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $eventSchedule);

        $this->removeEntity($eventSchedule);

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}