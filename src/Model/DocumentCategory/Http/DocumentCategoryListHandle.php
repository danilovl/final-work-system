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

namespace App\Model\DocumentCategory\Http;

use App\Model\MediaCategory\Facade\MediaCategoryFacade;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use App\Service\{
    UserService,
    PaginatorService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class DocumentCategoryListHandle
{
    public function __construct(
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private PaginatorService $paginatorService,
        private MediaCategoryFacade $mediaCategoryFacade,
        private ParameterServiceInterface $parameterService
    ) {
    }

    public function handle(Request $request): Response
    {
        $user = $this->userService->getUser();

        return $this->twigRenderService->render('document_category/list.html.twig', [
            'mediaCategories' => $this->paginatorService->createPaginationRequest(
                $request,
                $this->mediaCategoryFacade->queryMediaCategoriesByOwner($user),
                $this->parameterService->get('pagination.default.page'),
                $this->parameterService->get('pagination.document_category.limit')
            )
        ]);
    }
}
