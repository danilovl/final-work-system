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
use App\Domain\Work\Repository\Elastica\ElasticaWorkRepository;
use App\Application\Service\{
    PaginatorService,
    TwigRenderService
};
use App\Domain\User\Service\UserService;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Form\Factory\WorkFormFactory;
use App\Domain\Work\Helper\WorkFunctionHelper;
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
        private PaginatorService $paginatorService,
        private WorkFormFactory $workFormFactory,
        private ElasticaWorkRepository $elasticaWorkRepository,
        private FormDeleteFactory $deleteFactory
    ) {}

    public function __invoke(Request $request, string $type): Response
    {
        $user = $this->userService->getUser();
        $data = new WorkSearchModel;

        $form = $this->workFormFactory
            ->getSearchForm($type, $data)
            ->handleRequest($request);

        $works = $this->elasticaWorkRepository->filterWorkList($user, $type, $form);

        $workGroups = match ($type) {
            WorkUserTypeConstant::SUPERVISOR->value => WorkFunctionHelper::groupWorksByCategoryAndSorting($works),
            default => WorkFunctionHelper::groupWorksByDeadline($works),
        };

        $pagination = $this->paginatorService->createPaginationRequest($request, $workGroups);

        $deleteForms = [];
        foreach ($pagination as $entities) {
            $entities = $entities['works'] ?? $entities;

            /** @var Work $entity */
            foreach ($entities as $entity) {
                $deleteForms[$entity->getId()] = $this->deleteFactory->createDeleteForm($entity, 'work_delete')->createView();
            }
        }

        return $this->twigRenderService->renderToResponse('domain/work/list.html.twig', [
            'form' => $form->createView(),
            'openSearchTab' => $form->isSubmitted(),
            'workGroups' => $pagination,
            'deleteForms' => $deleteForms
        ]);
    }
}
