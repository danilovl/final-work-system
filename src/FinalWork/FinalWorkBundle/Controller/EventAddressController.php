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

use Doctrine\ORM\{
    ORMException,
    OptimisticLockException,
    NonUniqueResultException
};
use FinalWork\FinalWorkBundle\Exception\ConstantNotFoundException;
use FinalWork\FinalWorkBundle\Model\EventAddress\EventAddressModel;
use FinalWork\FinalWorkBundle\Constant\{
    FlashTypeConstant,
    VoterSupportConstant,
    ControllerMethodConstant
};
use FinalWork\FinalWorkBundle\Form\EventAddressForm;
use FinalWork\FinalWorkBundle\Entity\EventAddress;
use LogicException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EventAddressController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @throws LogicException
     */
    public function listAction(Request $request): Response
    {
        $this->get('final_work.seo_page')->setTitle('finalwork.page.appointment_address_list');

        return $this->render('@FinalWork/event_address/list.html.twig', [
            'eventAddresses' => $this->createPagination(
                $request,
                $this->getUser()->getEventAddressOwner()
            )
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(Request $request): Response
    {
        $eventAddressModel = new EventAddressModel;
        $eventAddressModel->owner = $this->getUser();

        $form = $this->getEventAddressForm(ControllerMethodConstant::CREATE, $eventAddressModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventAddress = $this->get('final_work.factory.event_address')
                ->flushFromModel($eventAddressModel);

            return $this->redirectToRoute('event_address_detail', [
                'id' => $this->hashIdEncode($eventAddress->getId())
            ]);
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getEventAddressForm(ControllerMethodConstant::CREATE_AJAX, $eventAddressModel);
        }

        $this->get('final_work.seo_page')->setTitle('finalwork.page.appointment_address_create');

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/event_address/event_address.html.twig'), [
            'form' => $form->createView(),
            'title' => $this->trans('finalwork.page.appointment_address_create'),
            'buttonActionTitle' => $this->trans('finalwork.form.action.create'),
            'buttonActionCloseTitle' => $this->trans('finalwork.form.action.create_and_close')
        ]);
    }

    /**
     * @param EventAddress $eventAddress
     * @return Response
     *
     * @throws AccessDeniedException
     * @throws LogicException
     */
    public function detailAction(EventAddress $eventAddress): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $eventAddress);

        $this->get('final_work.seo_page')->setTitle($eventAddress->getName());

        return $this->render('@FinalWork/event_address/detail.html.twig', [
            'eventAddress' => $eventAddress,
            'deleteForm' => $this->createDeleteForm($eventAddress, 'event_address_delete')->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param EventAddress $eventAddress
     * @return Response
     *
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(
        Request $request,
        EventAddress $eventAddress
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $eventAddress);

        $eventAddressModel = EventAddressModel::fromEventAddress($eventAddress);
        $form = $this->getEventAddressForm(ControllerMethodConstant::EDIT, $eventAddressModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($eventAddress->isSkype()) {
                $eventAddressSkype = $this->get('final_work.facade.event_address')
                    ->getSkypeByOwner($this->getUser());

                if ($eventAddressSkype !== null) {
                    $eventAddressSkype->setSkype(false);
                }
            }

            $this->get('final_work.factory.event_address')->flushFromModel($eventAddressModel, $eventAddress);

            return $this->redirectToRoute('event_address_list');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getEventAddressForm(
                ControllerMethodConstant::EDIT_AJAX,
                $eventAddressModel,
                $eventAddress
            );
        }

        $this->get('final_work.seo_page')->setTitle('finalwork.page.appointment_address_edit');

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/event_address/event_address.html.twig'), [
            'form' => $form->createView(),
            'eventAddress' => $eventAddress,
            'title' => $this->trans('finalwork.page.appointment_address_edit'),
            'buttonActionTitle' => $this->trans('finalwork.form.action.update'),
            'buttonActionCloseTitle' => $this->trans('finalwork.form.action.update_and_close')
        ]);
    }

    /**
     * @param Request $request
     * @param EventAddress $eventAddress
     *
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(
        Request $request,
        EventAddress $eventAddress
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $eventAddress);

        $form = $this->createDeleteForm($eventAddress, 'event_address_delete')
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->removeEntity($eventAddress);

                $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.delete.success');

                return $this->redirectToRoute('event_address_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.delete.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.delete.error');
        }

        return $this->redirectToRoute('event_address_list');
    }

    /**
     * @param string $type
     * @param EventAddressModel $eventAddressModel
     * @param EventAddress $eventAddress
     * @return FormInterface
     */
    public function getEventAddressForm(
        string $type,
        EventAddressModel $eventAddressModel,
        EventAddress $eventAddress = null
    ): FormInterface {
        $parameters = [];

        switch ($type) {
            case ControllerMethodConstant::EDIT:
            case ControllerMethodConstant::CREATE:
                break;
            case ControllerMethodConstant::CREATE_AJAX:
                $parameters = [
                    'action' => $this->generateUrl('event_address_create_ajax'),
                    'method' => Request::METHOD_POST
                ];
                break;
            case ControllerMethodConstant::EDIT_AJAX:
                $parameters = [
                    'action' => $this->generateUrl('event_address_edit_ajax', [
                        'id' => $this->hashIdEncode($eventAddress->getId())
                    ]),
                    'method' => Request::METHOD_POST
                ];
                break;
            default:
                throw new ConstantNotFoundException('Controller method type constant not found');
        }

        return $this->createForm(EventAddressForm::class, $eventAddressModel, $parameters);
    }
}
