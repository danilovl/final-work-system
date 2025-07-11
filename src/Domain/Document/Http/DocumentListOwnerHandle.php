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
use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\Document\Bus\Query\DocumentList\{
    GetDocumentListQuery,
    GetDocumentListQueryResult
};
use App\Infrastructure\Service\TwigRenderService;
use App\Domain\Document\Form\Factory\DocumentFormFactory;
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
        private DocumentFormFactory $documentFormFactory,
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $this->userService->getUser();
        $openSearchTab = false;

        $form = $this->documentFormFactory
            ->setUser($user)
            ->getDocumentForm(ControllerMethodConstant::LIST_OWNER)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $openSearchTab = true;
        }

        /** @var array<string, mixed>|null $criteria */
        $criteria = $form->isSubmitted() && $form->isValid() ? $form->getData() : null;

        $query = GetDocumentListQuery::create(
            request: $request,
            users: [$user],
            criteria: $criteria
        );

        /** @var GetDocumentListQueryResult $result */
        $result = $this->queryBus->handle($query);

        return $this->twigRenderService->renderToResponse('domain/document/list_owner.html.twig', [
            'openSearchTab' => $openSearchTab,
            'documents' => $result->documents,
            'form' => $form->createView()
        ]);
    }
}
