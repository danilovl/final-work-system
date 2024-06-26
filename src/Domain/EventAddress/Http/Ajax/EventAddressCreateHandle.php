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

namespace App\Domain\EventAddress\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Helper\FormValidationMessageHelper;
use App\Application\Service\RequestService;
use App\Domain\EventAddress\Factory\EventAddressFactory;
use App\Domain\EventAddress\Form\EventAddressForm;
use App\Domain\EventAddress\Model\EventAddressModel;
use App\Domain\User\Service\UserService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class EventAddressCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EventAddressFactory $eventAddressFactory,
        private FormFactoryInterface $formFactory
    ) {}

    public function __invoke(Request $request): JsonResponse
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
