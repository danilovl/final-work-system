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

namespace App\Domain\DocumentCategory\Http;

use App\Application\Service\{
    PaginatorService,
    TwigRenderService
};
use App\Domain\MediaCategory\Facade\MediaCategoryFacade;
use App\Domain\User\Service\UserService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class DocumentCategoryListHandle
{
    public function __construct(
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private PaginatorService $paginatorService,
        private MediaCategoryFacade $mediaCategoryFacade,
        private ParameterServiceInterface $parameterService
    ) {}

    public function handle(Request $request): Response
    {
        $user = $this->userService->getUser();

        return $this->twigRenderService->renderToResponse('document_category/list.html.twig', [
            'mediaCategories' => $this->paginatorService->createPaginationRequest(
                $request,
                $this->mediaCategoryFacade->queryMediaCategoriesByOwner($user),
                $this->parameterService->getInt('pagination.default.page'),
                $this->parameterService->getInt('pagination.document_category.limit')
            )
        ]);
    }
}
