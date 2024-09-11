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

namespace App\Domain\Document\Http;

use App\Application\Constant\ControllerMethodConstant;
use App\Application\Service\{
    EntityManagerService,
    PaginatorService,
    TwigRenderService
};
use App\Domain\Document\Form\Factory\DocumentFormFactory;
use App\Domain\Media\DataTransferObject\MediaRepositoryData;
use App\Domain\Media\Facade\MediaFacade;
use App\Domain\MediaType\Constant\MediaTypeConstant;
use App\Domain\MediaType\Entity\MediaType;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class DocumentListOwnerHandle
{
    public function __construct(
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private EntityManagerService $entityManagerService,
        private DocumentFormFactory $documentFormFactory,
        private PaginatorService $paginatorService,
        private MediaFacade $mediaFacade
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $this->userService->getUser();
        $openSearchTab = false;
        $criteria = null;

        $form = $this->documentFormFactory
            ->setUser($user)
            ->getDocumentForm(ControllerMethodConstant::LIST_OWNER)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $openSearchTab = true;
            $criteria = $form->getData();
        }

        /** @var MediaType $type */
        $type = $this->entityManagerService->getReference(MediaType::class, MediaTypeConstant::INFORMATION_MATERIAL->value);

        $mediaData = MediaRepositoryData::createFromArray([
            'users' => $user,
            'type' => $type,
            'criteria' => $criteria
        ]);

        $documents = $this->mediaFacade->getMediaListQueryByUserFilter($mediaData);

        return $this->twigRenderService->renderToResponse('domain/document/list_owner.html.twig', [
            'openSearchTab' => $openSearchTab,
            'documents' => $this->paginatorService->createPaginationRequest($request, $documents),
            'form' => $form->createView()
        ]);
    }
}
