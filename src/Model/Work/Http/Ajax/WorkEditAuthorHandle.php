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

use App\Model\Work\Entity\Work;
use App\Model\User\Form\UserEditForm;
use App\Helper\FormValidationMessageHelper;
use App\Model\User\Factory\UserFactory;
use App\Model\User\UserModel;
use App\Constant\AjaxJsonTypeConstant;
use App\Model\Work\EventDispatcher\WorkEventDispatcherService;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\RequestService;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class WorkEditAuthorHandle
{
    public function __construct(
        private RequestService $requestService,
        private FormFactoryInterface $formFactory,
        private WorkEventDispatcherService $workEventDispatcherService,
        private UserFactory $userFactory
    ) {
    }

    public function handle(Request $request, Work $work): JsonResponse
    {
        $author = $work->getAuthor();
        $userModel = UserModel::fromUser($author);

        $form = $this->formFactory
            ->create(UserEditForm::class, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userFactory->flushFromModel($userModel, $author);
            $this->workEventDispatcherService->onWorkEditAuthor($work);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
