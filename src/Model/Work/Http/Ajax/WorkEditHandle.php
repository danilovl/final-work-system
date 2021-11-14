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

use App\Entity\Work;
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

class WorkEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private FormFactoryInterface $formFactory,
        private WorkFactory $workFactory,
        private WorkEventDispatcherService $workEventDispatcherService
    ) {
    }

    public function handle(Request $request, Work $work): JsonResponse
    {
        $user = $this->userService->getUser();

        $workModel = WorkModel::fromWork($work);
        $form = $this->formFactory
            ->create(WorkForm::class, $workModel, ['user' => $user])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->workFactory->flushFromModel($workModel, $work);
            $this->workEventDispatcherService->onWorkEdit($work);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
