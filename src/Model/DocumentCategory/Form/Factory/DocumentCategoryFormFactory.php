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

namespace App\Model\DocumentCategory\Form\Factory;

use App\Constant\ControllerMethodConstant;
use App\Entity\MediaCategory;
use App\Exception\RuntimeException;
use App\Model\MediaCategory\MediaCategoryModel;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Routing\RouterInterface;
use App\Model\MediaCategory\Form\MediaCategoryForm;
use Symfony\Component\Form\{
    FormInterface,
    FormFactoryInterface
};
use Symfony\Component\HttpFoundation\Request;

class DocumentCategoryFormFactory
{
    public function __construct(
        private RouterInterface $router,
        private HashidsServiceInterface $hashidsService,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function getDocumentCategoryForm(
        string $type,
        MediaCategoryModel $mediaCategoryModel,
        MediaCategory $mediaCategory = null
    ): FormInterface {
        $parameters = [];

        switch ($type) {
            case ControllerMethodConstant::EDIT:
            case ControllerMethodConstant::CREATE:
                break;
            case ControllerMethodConstant::CREATE_AJAX:
                $parameters = [
                    'action' => $this->router->generate('document_category_create_ajax'),
                    'method' => Request::METHOD_POST
                ];
                break;
            case ControllerMethodConstant::EDIT_AJAX:
                $parameters = [
                    'action' => $this->router->generate('document_category_edit_ajax', [
                        'id' => $this->hashidsService->encode($mediaCategory->getId())
                    ]),
                    'method' => Request::METHOD_POST
                ];
                break;
            default:
                throw new RuntimeException('Controller method type not found');
        }

        return $this->formFactory->create(MediaCategoryForm::class, $mediaCategoryModel, $parameters);
    }
}
