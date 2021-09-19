<?php declare(strict_types=1);

/**
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Model\EventAddress\Http;

use App\Constant\ControllerMethodConstant;
use App\Form\Factory\EventAddressFormFactory;
use App\Model\EventAddress\EventAddressModel;
use App\Model\EventAddress\Factory\EventAddressFactory;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use App\Service\{
    UserService,
    RequestService,
    TranslatorService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class EventAddressCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TranslatorService $translatorService,
        private TwigRenderService $twigRenderService,
        private EventAddressFactory $eventAddressFactory,
        private EventAddressFormFactory $eventAddressFormFactory,
        private HashidsServiceInterface $hashidsService
    ) {
    }

    public function handle(Request $request): Response
    {
        $eventAddressModel = new EventAddressModel;
        $eventAddressModel->owner = $this->userService->getUser();

        $form = $this->eventAddressFormFactory
            ->getEventAddressForm(
                ControllerMethodConstant::CREATE,
                $eventAddressModel
            )
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventAddress = $this->eventAddressFactory
                ->flushFromModel($eventAddressModel);

            return $this->requestService->redirectToRoute('event_address_detail', [
                'id' => $this->hashidsService->encode($eventAddress->getId())
            ]);
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->eventAddressFormFactory->getEventAddressForm(
                ControllerMethodConstant::CREATE_AJAX,
                $eventAddressModel
            );
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'event_address/event_address.html.twig');

        return $this->twigRenderService->render($template, [
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.appointment_address_create'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.create_and_close')
        ]);
    }
}
