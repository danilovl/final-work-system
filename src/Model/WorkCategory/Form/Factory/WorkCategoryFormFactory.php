<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Model\WorkCategory\Form\Factory;

use App\Constant\ControllerMethodConstant;
use App\Model\WorkCategory\Entity\WorkCategory;
use App\Exception\ConstantNotFoundException;
use App\Model\WorkCategory\Form\WorkCategoryForm;
use App\Model\WorkCategory\WorkCategoryModel;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\{
    FormInterface,
    FormFactoryInterface
};
use Symfony\Component\HttpFoundation\Request;

class WorkCategoryFormFactory
{
    public function __construct(
        private RouterInterface $router,
        private HashidsServiceInterface $hashidsService,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function getWorkCategoryForm(
        string $type,
        WorkCategoryModel $workCategoryModel,
        WorkCategory $workCategory = null
    ): FormInterface {
        $parameters = [];

        switch ($type) {
            case ControllerMethodConstant::EDIT:
            case ControllerMethodConstant::CREATE:
                break;
            case ControllerMethodConstant::CREATE_AJAX:
                $parameters = [
                    'action' => $this->router->generate('work_category_create_ajax'),
                    'method' => Request::METHOD_POST
                ];

                break;
            case ControllerMethodConstant::EDIT_AJAX:
                $parameters = [
                    'action' => $this->router->generate('work_category_edit_ajax', [
                        'id' => $this->hashidsService->encode($workCategory->getId())
                    ]),
                    'method' => Request::METHOD_POST
                ];

                break;
            default:
                throw new ConstantNotFoundException('Controller method type not found');
        }

        return $this->formFactory->create(WorkCategoryForm::class, $workCategoryModel, $parameters);
    }
}