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

namespace App\Domain\EventAddress\Http;

use App\Application\Constant\ControllerMethodConstant;
use App\Application\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\EventAddress\Facade\EventAddressFacade;
use App\Domain\EventAddress\Factory\EventAddressFactory;
use App\Domain\EventAddress\Form\Factory\EventAddressFormFactory;
use App\Domain\EventAddress\Model\EventAddressModel;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class EventAddressEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private EventAddressFormFactory $eventAddressFormFactory,
        private EventAddressFacade $eventAddressFacade,
        private EventAddressFactory $eventAddressFactory,
        private TranslatorService $translatorService
    ) {}

    public function __invoke(Request $request, EventAddress $eventAddress): Response
    {
        $user = $this->userService->getUser();
        $eventAddressModel = EventAddressModel::fromEventAddress($eventAddress);

        $form = $this->eventAddressFormFactory
            ->getEventAddressForm(
                ControllerMethodConstant::EDIT,
                $eventAddressModel
            )
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($eventAddress->isSkype()) {
                $eventAddressSkype = $this->eventAddressFacade
                    ->getSkypeByOwner($user);

                $eventAddressSkype?->setSkype(false);
            }

            $this->eventAddressFactory->flushFromModel($eventAddressModel, $eventAddress);

            return $this->requestService->redirectToRoute('event_address_list');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->eventAddressFormFactory->getEventAddressForm(
                ControllerMethodConstant::EDIT_AJAX,
                $eventAddressModel,
                $eventAddress
            );
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/event_address/event_address.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'form' => $form->createView(),
            'eventAddress' => $eventAddress,
            'title' => $this->translatorService->trans('app.page.appointment_address_edit'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.update_and_close')
        ]);
    }
}
