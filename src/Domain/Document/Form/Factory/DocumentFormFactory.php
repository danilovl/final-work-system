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

namespace App\Domain\Document\Form\Factory;

use App\Application\Constant\{
    MediaTypeConstant
};
use App\Application\Constant\ControllerMethodConstant;
use App\Application\Exception\{
    RuntimeException
};
use App\Application\Exception\ConstantNotFoundException;
use App\Domain\Document\Form\{
    DocumentForm
};
use App\Domain\Document\Form\DocumentSearchForm;
use App\Domain\Media\Entity\Media;
use App\Domain\Media\Facade\MediaMimeTypeFacade;
use App\Domain\Media\MediaModel;
use App\Domain\MediaCategory\Facade\MediaCategoryFacade;
use App\Domain\User\Entity\User;
use App\Domain\User\Facade\UserFacade;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Form\{
    FormFactoryInterface,
    FormInterface
};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class DocumentFormFactory
{
    private ?User $user = null;

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly RouterInterface $router,
        private readonly HashidsServiceInterface $hashIds,
        private readonly UserFacade $userService,
        private readonly MediaMimeTypeFacade $mediaMimeTypeFacade,
        private readonly MediaCategoryFacade $mediaCategoryFacade
    ) {}

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDocumentForm(
        string $type,
        MediaModel $mediaModel = null,
        Media $media = null
    ): FormInterface {
        $user = $this->getUser();
        $formClass = DocumentForm::class;
        $mimeTypes = $this->mediaMimeTypeFacade->getFormValidationMimeTypes(true);

        switch ($type) {
            case ControllerMethodConstant::CREATE:
                $parameters = [
                    'user' => $user,
                    'uploadMedia' => true,
                    'mimeTypes' => $mimeTypes
                ];

                break;
            case ControllerMethodConstant::EDIT:
                if ($media === null) {
                    throw new RuntimeException('Media must not be null for edit');
                }

                $parameters = [
                    'action' => $this->router->generate('document_edit', [
                        'id' => $this->hashIds->encode($media->getId())
                    ]),
                    'method' => Request::METHOD_POST,
                    'user' => $user,
                    'uploadMedia' => false,
                    'mimeTypes' => $mimeTypes
                ];
                break;
            case ControllerMethodConstant::CREATE_AJAX:
                $parameters = [
                    'action' => $this->router->generate('document_create_ajax'),
                    'method' => Request::METHOD_POST,
                    'uploadMedia' => true,
                    'user' => $user,
                    'mimeTypes' => $mimeTypes
                ];
                break;
            case ControllerMethodConstant::EDIT_AJAX:
                if ($media === null) {
                    throw new RuntimeException('Media must not be null for edit ajax');
                }

                $parameters = [
                    'action' => $this->router->generate('document_edit_ajax', [
                        'id' => $this->hashIds->encode($media->getId())
                    ]),
                    'method' => Request::METHOD_POST,
                    'user' => $user,
                    'uploadMedia' => false,
                    'mimeTypes' => $mimeTypes
                ];
                break;
            case ControllerMethodConstant::LIST:
                $userActiveSupervisors = $this->userService
                    ->getAllUserActiveSupervisors($user);

                $categories = $this->mediaCategoryFacade
                    ->getMediaCategoriesByOwners($userActiveSupervisors);

                $mimeType = $this->mediaMimeTypeFacade
                    ->getMimeTypesByOwner($userActiveSupervisors, MediaTypeConstant::INFORMATION_MATERIAL);

                $formClass = DocumentSearchForm::class;
                $parameters = [
                    'categories' => $categories,
                    'mimeType' => $mimeType
                ];
                break;
            case ControllerMethodConstant::LIST_OWNER:
                $categories = $this->mediaCategoryFacade
                    ->getMediaCategoriesByOwner($user);

                $mimeType = $this->mediaMimeTypeFacade
                    ->getMimeTypesByOwner($user, MediaTypeConstant::INFORMATION_MATERIAL);

                $formClass = DocumentSearchForm::class;
                $parameters = [
                    'categories' => $categories,
                    'mimeType' => $mimeType
                ];
                break;
            default:
                throw new ConstantNotFoundException('Controller method type constant not found');
        }

        return $this->formFactory->create($formClass, $mediaModel, $parameters);
    }
}
