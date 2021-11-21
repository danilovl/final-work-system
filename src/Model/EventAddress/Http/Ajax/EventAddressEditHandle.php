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

namespace App\Model\EventAddress\Http\Ajax;

use App\Constant\AjaxJsonTypeConstant;
use App\Helper\FormValidationMessageHelper;
use App\Model\EventAddress\EventAddressModel;
use App\Model\EventAddress\Facade\EventAddressFacade;
use App\Model\EventAddress\Factory\EventAddressFactory;
use App\Model\EventAddress\Entity\EventAddress;
use App\Model\EventAddress\Form\EventAddressForm;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\{
    UserService,
    RequestService
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class EventAddressEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EventAddressFacade $eventAddressFacade,
        private EventAddressFactory $eventAddressFactory,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function handle(Request $request, EventAddress $eventAddress): JsonResponse
    {
        $eventAddressModel = EventAddressModel::fromEventAddress($eventAddress);
        $form = $this->formFactory
            ->create(EventAddressForm::class, $eventAddressModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($eventAddress->isSkype()) {
                $eventAddressSkype = $this->eventAddressFacade->getSkypeByOwner(
                    $this->userService->getUser()
                );

                $eventAddressSkype?->setSkype(false);
            }

            $this->eventAddressFactory
                ->flushFromModel($eventAddressModel, $eventAddress);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
