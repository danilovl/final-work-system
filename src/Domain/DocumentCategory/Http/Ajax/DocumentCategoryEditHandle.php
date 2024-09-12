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
use App\Application\Service\RequestService;
use App\Domain\MediaCategory\Entity\MediaCategory;
use App\Domain\MediaCategory\Factory\MediaCategoryFactory;
use App\Domain\MediaCategory\Form\MediaCategoryForm;
use App\Domain\MediaCategory\Model\MediaCategoryModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class DocumentCategoryEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private FormFactoryInterface $formFactory,
        private MediaCategoryFactory $mediaCategoryFactory
    ) {}

    public function __invoke(Request $request, MediaCategory $mediaCategory): JsonResponse
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
