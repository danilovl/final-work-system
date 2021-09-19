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
use App\Entity\MediaCategory;
use App\Form\MediaCategoryForm;
use App\Helper\FormValidationMessageHelper;
use App\Model\MediaCategory\Factory\MediaCategoryFactory;
use App\Model\MediaCategory\MediaCategoryModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Service\RequestService;
use Symfony\Component\HttpFoundation\JsonResponse;

class DocumentCategoryEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private FormFactoryInterface $formFactory,
        private MediaCategoryFactory $mediaCategoryFactory
    ) {
    }

    public function handle(Request $request, MediaCategory $mediaCategory): JsonResponse
    {
        $mediaCategoryModel = MediaCategoryModel::fromMediaCategory($mediaCategory);
        $form = $this->formFactory
            ->create(MediaCategoryForm::class, $mediaCategoryModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->mediaCategoryFactory
                ->flushFromModel($mediaCategoryModel, $mediaCategory);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
