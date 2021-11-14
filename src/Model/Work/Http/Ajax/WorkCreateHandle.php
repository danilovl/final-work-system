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

namespace App\Model\Work\Http\Ajax;

use App\Model\Work\EventDispatcher\WorkEventDispatcherService;
use App\Model\Work\Form\WorkForm;
use App\Constant\AjaxJsonTypeConstant;
use App\Helper\FormValidationMessageHelper;
use App\Model\Work\Factory\WorkFactory;
use App\Model\Work\WorkModel;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\{
    UserService,
    RequestService
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class WorkCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private FormFactoryInterface $formFactory,
        private WorkFactory $workFactory,
        private WorkEventDispatcherService $workEventDispatcherService
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $user = $this->userService->getUser();

        $workModel = new WorkModel;
        $workModel->supervisor = $user;

        $form = $this->formFactory
            ->create(WorkForm::class, $workModel, ['user' => $user])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $work = $this->workFactory->flushFromModel($workModel);
            $this->workEventDispatcherService->onWorkCreate($work);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
