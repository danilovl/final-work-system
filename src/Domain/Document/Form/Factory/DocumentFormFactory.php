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

namespace App\Domain\Document\Form\Factory;

use App\Application\Constant\ControllerMethodConstant;
use App\Application\Exception\{
    RuntimeException,
    ConstantNotFoundException
};
use App\Domain\Document\Form\{
    DocumentForm,
    DocumentSearchForm
};
use App\Domain\Media\Entity\Media;
use App\Domain\Media\Facade\MediaMimeTypeFacade;
use App\Domain\Media\Model\MediaModel;
use App\Domain\MediaCategory\Facade\MediaCategoryFacade;
use App\Domain\MediaType\Constant\MediaTypeConstant;
use App\Domain\User\Entity\User;
use App\Domain\User\Facade\UserFacade;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Form\{
    FormInterface,
    FormFactoryInterface
};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class DocumentFormFactory
{
    private User $user;

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly RouterInterface $router,
        private readonly HashidsServiceInterface $hashIds,
        private readonly UserFacade $userService,
        private readonly MediaMimeTypeFacade $mediaMimeTypeFacade,
        private readonly MediaCategoryFacade $mediaCategoryFacade
    ) {}

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDocumentForm(
        ControllerMethodConstant $type,
        ?MediaModel $mediaModel = null,
        ?Media $media = null
    ): FormInterface {
        $user = $this->user;
        $formClass = DocumentForm::class;
        $mimeTypes = $this->mediaMimeTypeFacade->list(true);

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
                    ->listUserActiveSupervisors($user);

                $categories = $this->mediaCategoryFacade
                    ->listByOwners($userActiveSupervisors);

                $mimeType = $this->mediaMimeTypeFacade
                    ->listByOwner($userActiveSupervisors, MediaTypeConstant::INFORMATION_MATERIAL->value);

                $formClass = DocumentSearchForm::class;
                $parameters = [
                    'categories' => $categories,
                    'mimeType' => $mimeType
                ];

                break;
            case ControllerMethodConstant::LIST_OWNER:
                $categories = $this->mediaCategoryFacade
                    ->listByOwner($user);

                $mimeType = $this->mediaMimeTypeFacade
                    ->listByOwner($user, MediaTypeConstant::INFORMATION_MATERIAL->value);

                $formClass = DocumentSearchForm::class;
                $parameters = [
                    'categories' => $categories,
                    'mimeType' => $mimeType
                ];

                break;
            default:
                throw new ConstantNotFoundException('Controller method type constant not found');
        }

        return $this->formFactory->create(type: $formClass, data: $mediaModel, options: $parameters);
    }
}
