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

namespace App\Domain\Work\Http;

use App\Application\Form\Factory\FormDeleteFactory;
use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\Work\Bus\Query\WorkList\{
    GetWorkListQuery,
    GetWorkListQueryResult
};
use App\Application\Service\TwigRenderService;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Form\Factory\WorkFormFactory;
use App\Domain\WorkSearch\Model\WorkSearchModel;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class WorkListHandle
{
    public function __construct(
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private FormDeleteFactory $deleteFactory,
        private WorkFormFactory $workFormFactory,
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(Request $request, string $type): Response
    {
        $user = $this->userService->getUser();
        $data = new WorkSearchModel;

        $form = $this->workFormFactory
            ->getSearchForm($type, $data)
            ->handleRequest($request);

        $query = GetWorkListQuery::create(
            request: $request,
            user: $user,
            type: $type,
            searchData: $form->isSubmitted() && $form->isValid() ? (array) $form->getData() : []
        );

        /** @var GetWorkListQueryResult $result */
        $result = $this->queryBus->handle($query);

        $deleteForms = [];
        foreach ($result->workGroups as $entities) {
            $entities = $entities['works'] ?? $entities;

            /** @var Work $entity */
            foreach ($entities as $entity) {
                $deleteForms[$entity->getId()] = $this->deleteFactory
                    ->createDeleteForm($entity, 'work_delete')
                    ->createView();
            }
        }

        return $this->twigRenderService->renderToResponse('domain/work/list.html.twig', [
            'form' => $form->createView(),
            'openSearchTab' => $form->isSubmitted(),
            'workGroups' => $result->workGroups,
            'deleteForms' => $deleteForms
        ]);
    }
}
