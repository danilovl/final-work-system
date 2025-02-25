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

use App\Application\Constant\{
    FlashTypeConstant,
    ControllerMethodConstant
};
use App\Application\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\EventAddress\Factory\EventAddressFactory;
use App\Domain\EventAddress\Form\Factory\EventAddressFormFactory;
use App\Domain\EventAddress\Model\EventAddressModel;
use App\Domain\User\Service\UserService;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class EventAddressCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TranslatorService $translatorService,
        private TwigRenderService $twigRenderService,
        private EventAddressFactory $eventAddressFactory,
        private EventAddressFormFactory $eventAddressFormFactory,
        private HashidsServiceInterface $hashidsService
    ) {}

    public function __invoke(Request $request): Response
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

            $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.create.success');

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

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/event_address/event_address.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.appointment_address_create'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.create_and_close')
        ]);
    }
}
