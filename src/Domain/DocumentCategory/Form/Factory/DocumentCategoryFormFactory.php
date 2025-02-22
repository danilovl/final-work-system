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

namespace App\Domain\DocumentCategory\Form\Factory;

use App\Application\Constant\ControllerMethodConstant;
use App\Application\Exception\RuntimeException;
use App\Domain\MediaCategory\Entity\MediaCategory;
use App\Domain\MediaCategory\Form\MediaCategoryForm;
use App\Domain\MediaCategory\Model\MediaCategoryModel;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Form\{
    FormInterface,
    FormFactoryInterface
};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class DocumentCategoryFormFactory
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly HashidsServiceInterface $hashidsService,
        private readonly FormFactoryInterface $formFactory
    ) {}

    public function getDocumentCategoryForm(
        ControllerMethodConstant $type,
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
                if ($mediaCategory === null) {
                    throw new RuntimeException('Media category is null.');
                }

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
