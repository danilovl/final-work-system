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
use App\Controller\BaseController;
use App\Form\EventAddressForm;
use App\Helper\FormValidationMessageHelper;
use App\Entity\EventAddress;
use App\Model\EventAddress\EventAddressModel;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class EventAddressController extends BaseController
{
    public function create(Request $request): JsonResponse
    {
        $eventAddressModel = new EventAddressModel;
        $eventAddressModel->owner = $this->getUser();

        $form = $this->createForm(EventAddressForm::class, $eventAddressModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.factory.event_address')
                ->flushFromModel($eventAddressModel);

            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    public function edit(
        Request $request,
        EventAddress $eventAddress
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $eventAddress);

        $eventAddressModel = EventAddressModel::fromEventAddress($eventAddress);
        $form = $this->createForm(EventAddressForm::class, $eventAddressModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($eventAddress->isSkype()) {
                $eventAddressSkype = $this->get('app.facade.event_address')
                    ->getSkypeByOwner($this->getUser());

                if ($eventAddressSkype !== null) {
                    $eventAddressSkype->setSkype(false);
                }
            }

            $this->get('app.factory.event_address')
                ->flushFromModel($eventAddressModel, $eventAddress);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    public function delete(EventAddress $eventAddress): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $eventAddress);

        $this->removeEntity($eventAddress);

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
