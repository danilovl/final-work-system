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

namespace FinalWork\FinalWorkBundle\Services;

use FinalWork\FinalWorkBundle\Exception\{
    RuntimeException,
    ConstantNotFoundException
};
use FinalWork\FinalWorkBundle\Model\Media\{
    MediaModel,
    MediaCategoryFacade,
    MediaMimeTypeFacade
};
use FinalWork\FinalWorkBundle\Model\User\UserFacade;
use FinalWork\FinalWorkBundle\Constant\{
    MediaTypeConstant,
    ControllerMethodConstant
};
use FinalWork\FinalWorkBundle\Entity\Media;
use FinalWork\FinalWorkBundle\Form\{
    DocumentForm,
    DocumentSearchForm
};
use FinalWork\SonataUserBundle\Entity\User;
use Hashids\Hashids;
use Symfony\Component\Form\{
    FormInterface,
    FormFactoryInterface
};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

class DocumentFormService
{
    /**
     * @var User|null
     */
    private $user;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Hashids
     */
    private $hashIds;

    /**
     * @var UserFacade
     */
    private $userService;

    /**
     * @var MediaMimeTypeFacade
     */
    private $mediaMimeTypeFacade;

    /**
     * @var MediaCategoryFacade
     */
    private $mediaCategoryFacade;

    /**
     * DocumentFormService constructor.
     * @param FormFactoryInterface $formFactory
     * @param Router $router
     * @param Hashids $hashIds
     * @param UserFacade $userService
     * @param MediaMimeTypeFacade $mediaMimeTypeFacade
     * @param MediaCategoryFacade $mediaCategoryFacade
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        Router $router,
        Hashids $hashIds,
        UserFacade $userService,
        MediaMimeTypeFacade $mediaMimeTypeFacade,
        MediaCategoryFacade $mediaCategoryFacade
    ) {
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->hashIds = $hashIds;
        $this->userService = $userService;
        $this->mediaMimeTypeFacade = $mediaMimeTypeFacade;
        $this->mediaCategoryFacade = $mediaCategoryFacade;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return DocumentFormService
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param string $type
     * @param MediaModel|null $mediaModel
     * @param Media|null $media
     * @return FormInterface
     */
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
