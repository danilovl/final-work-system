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
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException,
    NonUniqueResultException
};
use FinalWork\FinalWorkBundle\Controller\BaseController;
use FinalWork\FinalWorkBundle\Form\EventAddressForm;
use FinalWork\FinalWorkBundle\Helper\FormValidationMessageHelper;
use FinalWork\FinalWorkBundle\Entity\EventAddress;
use FinalWork\FinalWorkBundle\Model\EventAddress\EventAddressModel;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use LogicException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\Exception\InvalidArgumentException;

class EventAddressController extends BaseController
{
    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(Request $request): JsonResponse
    {
        $eventAddressModel = new EventAddressModel;
        $eventAddressModel->owner = $this->getUser();

        $form = $this->createForm(EventAddressForm::class, $eventAddressModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('final_work.factory.event_address')
                ->flushFromModel($eventAddressModel);

            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param Request $request
     * @param EventAddress $eventAddress
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(
        Request $request,
        EventAddress $eventAddress
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $eventAddress);

        $eventAddressModel = EventAddressModel::fromEventAddress($eventAddress);
        $form = $this->createForm(EventAddressForm::class, $eventAddressModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($eventAddress->isSkype()) {
                $eventAddressSkype = $this->get('final_work.facade.event_address')
                    ->getSkypeByOwner($this->getUser());

                if ($eventAddressSkype !== null) {
                    $eventAddressSkype->setSkype(false);
                }
            }

            $this->get('final_work.factory.event_address')
                ->flushFromModel($eventAddressModel, $eventAddress);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param EventAddress $eventAddress
     * @return JsonResponse
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     * @throws AccessDeniedException
     */
    public function deleteAction(EventAddress $eventAddress): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $eventAddress);

        $this->removeEntity($eventAddress);

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
