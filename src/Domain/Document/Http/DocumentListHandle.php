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
use App\Application\Service\{
    UserService,
    PaginatorService,
    TwigRenderService,
    EntityManagerService
};
use App\Domain\Document\Form\Factory\DocumentFormFactory;
use App\Domain\Media\DataTransferObject\MediaRepositoryData;
use App\Domain\Media\Facade\MediaFacade;
use App\Domain\MediaType\Entity\MediaType;
use App\Domain\User\Facade\UserFacade;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class DocumentListHandle
{
    public function __construct(
        private readonly UserService $userService,
        private readonly UserFacade $userFacade,
        private readonly TwigRenderService $twigRenderService,
        private readonly MediaFacade $mediaFacade,
        private readonly EntityManagerService $entityManagerService,
        private readonly DocumentFormFactory $documentFormFactory,
        private readonly PaginatorService $paginatorService
    ) {}

    public function handle(Request $request): Response
    {
        $user = $this->userService->getUser();

        $openSearchTab = false;
        $criteria = null;

        $form = $this->documentFormFactory
            ->setUser($user)
            ->getDocumentForm(ControllerMethodConstant::LIST)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $openSearchTab = true;
            $criteria = $form->getData();
        }

        /** @var MediaType $type */
        $type = $this->entityManagerService->getReference(MediaType::class, MediaTypeConstant::INFORMATION_MATERIAL);

        $mediaData = MediaRepositoryData::createFromArray([
            'users' => $this->userFacade->getAllUserActiveSupervisors($user),
            'type' => $type,
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
