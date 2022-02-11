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

use App\Application\Constant\{
    MediaTypeConstant,
    ControllerMethodConstant
};
use App\Application\DataTransferObject\Repository\MediaData;
use App\Application\Service\{
    UserService,
    PaginatorService,
    TwigRenderService,
    EntityManagerService
};
use App\Domain\Document\Form\Factory\DocumentFormFactory;
use App\Domain\Media\Facade\MediaFacade;
use App\Domain\MediaType\Entity\MediaType;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class DocumentListOwnerHandle
{
    public function __construct(
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private EntityManagerService $entityManagerService,
        private DocumentFormFactory $documentFormFactory,
        private PaginatorService $paginatorService,
        private MediaFacade $mediaFacade
    ) {
    }

    public function handle(Request $request): Response
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

        $mediaData = MediaData::createFromArray([
            'users' => $user,
            'type' => $this->entityManagerService->getReference(MediaType::class, MediaTypeConstant::INFORMATION_MATERIAL),
            'criteria' => $criteria
        ]);

        $documents = $this->mediaFacade->getMediaListQueryByUserFilter($mediaData);

        return $this->twigRenderService->render('document/list_owner.html.twig', [
            'openSearchTab' => $openSearchTab,
            'documents' => $this->paginatorService->createPaginationRequest($request, $documents),
            'form' => $form->createView()
        ]);
    }
}
