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

namespace App\Domain\WorkCategory\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Helper\FormValidationMessageHelper;
use App\Infrastructure\Service\RequestService;
use App\Domain\WorkCategory\Entity\WorkCategory;
use App\Domain\WorkCategory\Factory\WorkCategoryFactory;
use App\Domain\WorkCategory\Form\WorkCategoryForm;
use App\Domain\WorkCategory\Model\WorkCategoryModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class WorkCategoryEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private WorkCategoryFactory $workCategoryFactory,
        private FormFactoryInterface $formFactory
    ) {}

    public function __invoke(Request $request, WorkCategory $workCategory): JsonResponse
    {
        $workCategoryModel = WorkCategoryModel::fromMedia($workCategory);
        $form = $this->formFactory
            ->create(WorkCategoryForm::class, $workCategoryModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->workCategoryFactory
                ->flushFromModel($workCategoryModel, $workCategory);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
