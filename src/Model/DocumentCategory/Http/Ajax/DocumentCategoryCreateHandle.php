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

namespace App\Model\DocumentCategory\Http\Ajax;

use App\Constant\AjaxJsonTypeConstant;
use App\Model\MediaCategory\Form\MediaCategoryForm;
use App\Helper\FormValidationMessageHelper;
use App\Model\MediaCategory\Factory\MediaCategoryFactory;
use App\Model\MediaCategory\MediaCategoryModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Service\{
    UserService,
    RequestService
};
use Symfony\Component\HttpFoundation\JsonResponse;

class DocumentCategoryCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private FormFactoryInterface $formFactory,
        private MediaCategoryFactory $mediaCategoryFactory
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $mediaCategoryModel = new MediaCategoryModel;
        $mediaCategoryModel->owner = $this->userService->getUser();

        $form = $this->formFactory
            ->create(MediaCategoryForm::class, $mediaCategoryModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->mediaCategoryFactory->flushFromModel($mediaCategoryModel);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
