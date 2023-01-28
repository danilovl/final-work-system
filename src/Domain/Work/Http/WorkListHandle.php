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

use App\Application\Constant\WorkUserTypeConstant;
use App\Application\ElasticSearch\WorkSearch;
use App\Application\Form\Factory\FormDeleteFactory;
use App\Application\Helper\WorkFunctionHelper;
use App\Application\Service\{
    UserService,
    PaginatorService,
    TwigRenderService
};
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Form\Factory\WorkFormFactory;
use App\Domain\WorkSearch\WorkSearchModel;
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
        private WorkSearch $workSearch,
        private FormDeleteFactory $deleteFactory
    ) {}

    public function handle(Request $request, string $type): Response
    {
        $user = $this->userService->getUser();

        $form = $this->workFormFactory
            ->getSearchForm($user, $type, new WorkSearchModel)
            ->handleRequest($request);

        $works = $this->workSearch->filterWorkList($user, $type, $form);

        $workGroups = match ($type) {
            WorkUserTypeConstant::SUPERVISOR => WorkFunctionHelper::groupWorksByCategoryAndSorting($works),
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

        return $this->twigRenderService->render('work/list.html.twig', [
            'form' => $form->createView(),
            'openSearchTab' => $form->isSubmitted(),
            'workGroups' => $pagination,
            'deleteForms' => $deleteForms
        ]);
    }
}
