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

namespace App\Model\Document\Http;

use App\Constant\{
    MediaTypeConstant,
    ControllerMethodConstant
};
use App\DataTransferObject\Repository\MediaData;
use App\Entity\MediaType;
use App\Model\Document\Form\Factory\DocumentFormFactory;
use App\Model\Media\Facade\MediaFacade;
use App\Model\User\Facade\UserFacade;
use App\Service\{EntityManagerService,
    UserService,
    PaginatorService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class DocumentListHandle
{
    public function __construct(
        private UserService $userService,
        private UserFacade $userFacade,
        private TwigRenderService $twigRenderService,
        private MediaFacade $mediaFacade,
        private EntityManagerService $entityManagerService,
        private DocumentFormFactory $documentFormFactory,
        private PaginatorService $paginatorService
    ) {
    }

    public function handle(Request $request): Response
    {
        $user = $this->userService->getUser();

        $openSearchTab = false;
        $criteria = null;

        $form = $this->documentFormFactory
            ->getDocumentForm(ControllerMethodConstant::LIST)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $openSearchTab = true;
            $criteria = $form->getData();
        }

        $mediaData = MediaData::createFromArray([
            'users' => $this->userFacade->getAllUserActiveSupervisors($user),
            'type' => $this->entityManagerService->getReference(MediaType::class, MediaTypeConstant::INFORMATION_MATERIAL),
            'active' => true,
            'criteria' => $criteria
        ]);

        $documents = $this->mediaFacade->getMediaListQueryByUserFilter($mediaData);

        return $this->twigRenderService->render('document/list.html.twig', [
            'openSearchTab' => $openSearchTab,
            'documents' => $this->paginatorService->createPaginationRequest($request, $documents),
            'form' => $form->createView()
        ]);
    }
}
