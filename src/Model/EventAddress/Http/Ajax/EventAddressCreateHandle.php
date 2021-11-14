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
use App\Model\EventAddress\Factory\EventAddressFactory;
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

class EventAddressCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EventAddressFactory $eventAddressFactory,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $eventAddressModel = new EventAddressModel;
        $eventAddressModel->owner = $this->userService->getUser();

        $form = $this->formFactory
            ->create(EventAddressForm::class, $eventAddressModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventAddressFactory->flushFromModel($eventAddressModel);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
