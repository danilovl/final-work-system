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

namespace App\Form\Factory;

use App\Model\Media\Facade\MediaMimeTypeFacade;
use App\Model\MediaCategory\Facade\MediaCategoryFacade;
use App\Model\User\Facade\UserFacade;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use App\Exception\{
    RuntimeException,
    ConstantNotFoundException
};
use App\Model\Media\MediaModel;
use App\Constant\{
    MediaTypeConstant,
    ControllerMethodConstant
};
use App\Entity\Media;
use App\Form\{
    DocumentForm,
    DocumentSearchForm
};
use App\Entity\User;
use Symfony\Component\Form\{
    FormInterface,
    FormFactoryInterface
};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class DocumentFormFactory
{
    private ?User $user = null;

    public function __construct(
        private FormFactoryInterface $formFactory,
        private RouterInterface $router,
        private HashidsServiceInterface $hashIds,
        private UserFacade $userService,
        private MediaMimeTypeFacade $mediaMimeTypeFacade,
        private MediaCategoryFacade $mediaCategoryFacade
    ) {
    }

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
