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

use App\Exception\ConstantNotFoundException;
use App\Model\EventAddress\EventAddressModel;
use App\Constant\{
    FlashTypeConstant,
    VoterSupportConstant,
    ControllerMethodConstant
};
use App\Form\EventAddressForm;
use App\Entity\EventAddress;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class EventAddressController extends BaseController
{
    public function list(Request $request): Response
    {
        $this->get('app.seo_page')->setTitle('app.page.appointment_address_list');

        return $this->render('event_address/list.html.twig', [
            'eventAddresses' => $this->createPagination(
                $request,
                $this->getUser()->getEventAddressOwner()
            )
        ]);
    }

    public function create(Request $request): Response
    {
        $eventAddressModel = new EventAddressModel;
        $eventAddressModel->owner = $this->getUser();

        $form = $this->getEventAddressForm(ControllerMethodConstant::CREATE, $eventAddressModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventAddress = $this->get('app.factory.event_address')
                ->flushFromModel($eventAddressModel);

            return $this->redirectToRoute('event_address_detail', [
                'id' => $this->hashIdEncode($eventAddress->getId())
            ]);
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getEventAddressForm(ControllerMethodConstant::CREATE_AJAX, $eventAddressModel);
        }

        $this->get('app.seo_page')->setTitle('app.page.appointment_address_create');

        return $this->render($this->ajaxOrNormalFolder($request, 'event_address/event_address.html.twig'), [
            'form' => $form->createView(),
            'title' => $this->trans('app.page.appointment_address_create'),
            'buttonActionTitle' => $this->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.create_and_close')
        ]);
    }

    public function detail(EventAddress $eventAddress): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $eventAddress);

        $this->get('app.seo_page')->setTitle($eventAddress->getName());

        return $this->render('event_address/detail.html.twig', [
            'eventAddress' => $eventAddress,
            'deleteForm' => $this->createDeleteForm($eventAddress, 'event_address_delete')->createView()
        ]);
    }

    public function edit(
        Request $request,
        EventAddress $eventAddress
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $eventAddress);

        $eventAddressModel = EventAddressModel::fromEventAddress($eventAddress);
        $form = $this->getEventAddressForm(ControllerMethodConstant::EDIT, $eventAddressModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($eventAddress->isSkype()) {
                $eventAddressSkype = $this->get('app.facade.event_address')
                    ->getSkypeByOwner($this->getUser());

                if ($eventAddressSkype !== null) {
                    $eventAddressSkype->setSkype(false);
                }
            }

            $this->get('app.factory.event_address')->flushFromModel($eventAddressModel, $eventAddress);

            return $this->redirectToRoute('event_address_list');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getEventAddressForm(
                ControllerMethodConstant::EDIT_AJAX,
                $eventAddressModel,
                $eventAddress
            );
        }

        $this->get('app.seo_page')->setTitle('app.page.appointment_address_edit');

        return $this->render($this->ajaxOrNormalFolder($request, 'event_address/event_address.html.twig'), [
            'form' => $form->createView(),
            'eventAddress' => $eventAddress,
            'title' => $this->trans('app.page.appointment_address_edit'),
            'buttonActionTitle' => $this->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.update_and_close')
        ]);
    }

    public function delete(
        Request $request,
        EventAddress $eventAddress
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $eventAddress);

        $form = $this->createDeleteForm($eventAddress, 'event_address_delete')
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->removeEntity($eventAddress);

                $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.delete.success');

                return $this->redirectToRoute('event_address_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.delete.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.delete.error');
        }

        return $this->redirectToRoute('event_address_list');
    }

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
