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
use App\Domain\Media\Model\MediaModel;
use App\Domain\MediaType\Constant\MediaTypeConstant;
use App\Domain\User\Entity\User;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Form\{
    FormInterface,
    FormFactoryInterface
};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\Work\Bus\Query\ActiveSupervisors\{GetActiveSupervisorsQuery, GetActiveSupervisorsQueryResult};
use App\Domain\MediaMimeType\Bus\Query\MediaMimeTypeList\{GetMediaMimeTypeListQuery, GetMediaMimeTypeListQueryResult};
use App\Domain\MediaCategory\Bus\Query\MediaCategoryListByOwner\{GetMediaCategoryListByOwnerQuery, GetMediaCategoryListByOwnerQueryResult};
use App\Domain\MediaMimeType\Bus\Query\MediaMimeTypeListByOwner\{GetMediaMimeTypeListByOwnerQuery, GetMediaMimeTypeListByOwnerQueryResult};
use App\Domain\MediaCategory\Bus\Query\MediaCategoryListByOwners\{GetMediaCategoryListByOwnersQuery, GetMediaCategoryListByOwnersQueryResult};

class DocumentFormFactory
{
    private User $user;

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly RouterInterface $router,
        private readonly HashidsServiceInterface $hashIds,
        private readonly QueryBusInterface $queryBus
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
        $getMediaMimeTypeListQuery = GetMediaMimeTypeListQuery::create(true);
        /** @var GetMediaMimeTypeListQueryResult $getMediaMimeTypeListQueryResult */
        $getMediaMimeTypeListQueryResult = $this->queryBus->handle($getMediaMimeTypeListQuery);
        $mimeTypes = $getMediaMimeTypeListQueryResult->mediaMimeTypes;

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
                $getActiveSupervisorsQuery = GetActiveSupervisorsQuery::create($user);
                /** @var GetActiveSupervisorsQueryResult $getActiveSupervisorsQueryResult */
                $getActiveSupervisorsQueryResult = $this->queryBus->handle($getActiveSupervisorsQuery);
                $userActiveSupervisors = $getActiveSupervisorsQueryResult->supervisors;

                $getMediaCategoryListByOwnersQuery = GetMediaCategoryListByOwnersQuery::create($userActiveSupervisors);
                /** @var GetMediaCategoryListByOwnersQueryResult $getMediaCategoryListByOwnersQueryResult */
                $getMediaCategoryListByOwnersQueryResult = $this->queryBus->handle($getMediaCategoryListByOwnersQuery);
                $categories = $getMediaCategoryListByOwnersQueryResult->mediaCategories;

                $getSupervisorsMimeTypeQuery = GetMediaMimeTypeListByOwnerQuery::create(
                    user: $userActiveSupervisors,
                    mediaType: MediaTypeConstant::INFORMATION_MATERIAL->value,
                    onlyKey: false
                );
                /** @var GetMediaMimeTypeListByOwnerQueryResult $getSupervisorsMimeTypeQueryResult */
                $getSupervisorsMimeTypeQueryResult = $this->queryBus->handle($getSupervisorsMimeTypeQuery);
                $mimeType = $getSupervisorsMimeTypeQueryResult->mediaMimeTypes;

                $formClass = DocumentSearchForm::class;
                $parameters = [
                    'categories' => $categories,
                    'mimeType' => $mimeType
                ];

                break;
            case ControllerMethodConstant::LIST_OWNER:
                $getMediaCategoryListByOwnerQuery = GetMediaCategoryListByOwnerQuery::create($user);
                /** @var GetMediaCategoryListByOwnerQueryResult $getMediaCategoryListByOwnerQueryResult */
                $getMediaCategoryListByOwnerQueryResult = $this->queryBus->handle($getMediaCategoryListByOwnerQuery);
                $categories = $getMediaCategoryListByOwnerQueryResult->mediaCategories;

                $getMediaMimeTypeListByOwnerQuery = GetMediaMimeTypeListByOwnerQuery::create(
                    user: $user,
                    mediaType: MediaTypeConstant::INFORMATION_MATERIAL->value,
                    onlyKey: false
                );
                /** @var GetMediaMimeTypeListByOwnerQueryResult $getMediaMimeTypeListByOwnerQueryResult */
                $getMediaMimeTypeListByOwnerQueryResult = $this->queryBus->handle($getMediaMimeTypeListByOwnerQuery);
                $mimeType = $getMediaMimeTypeListByOwnerQueryResult->mediaMimeTypes;

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
