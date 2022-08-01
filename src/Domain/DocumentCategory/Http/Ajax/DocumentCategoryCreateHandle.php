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

namespace App\Domain\DocumentCategory\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Helper\FormValidationMessageHelper;
use App\Application\Service\{
    UserService,
    RequestService
};
use App\Domain\MediaCategory\Factory\MediaCategoryFactory;
use App\Domain\MediaCategory\Form\MediaCategoryForm;
use App\Domain\MediaCategory\MediaCategoryModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DocumentCategoryCreateHandle
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly UserService $userService,
        private readonly FormFactoryInterface $formFactory,
        private readonly MediaCategoryFactory $mediaCategoryFactory
    ) {}

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
