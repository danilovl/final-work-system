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

namespace App\Domain\Task\Http;

use App\Application\Form\SimpleSearchForm;
use App\Application\Model\SearchModel;
use App\Application\Service\{
    PaginatorService,
    TwigRenderService
};
use App\Domain\Task\Elastica\TaskSearch;
use App\Domain\Task\Facade\TaskFacade;
use App\Domain\User\Service\UserService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class TaskListHandle
{
    public function __construct(
        private TwigRenderService $twigRenderService,
        private UserService $userService,
        private TaskFacade $taskFacade,
        private PaginatorService $paginatorService,
        private FormFactoryInterface $formFactory,
        private TaskSearch $taskSearch
    ) {}

    public function handle(Request $request): Response
    {
        $user = $this->userService->getUser();
        $tasksQuery = $this->taskFacade
            ->queryTasksByOwner($user);

        $isTasksInComplete = $this->taskFacade
            ->isTasksCompleteByOwner($user, false);

        $searchModel = new SearchModel;
        $searchForm = $this->formFactory
            ->create(SimpleSearchForm::class, $searchModel)
            ->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $taskIds = $this->taskSearch->getIdsByOwnerAndSearch($user, $searchModel->search);
            $tasksQuery = $this->taskFacade->queryByIds($taskIds);
        }

        return $this->twigRenderService->renderToResponse('task/list.html.twig', [
            'isTasksInComplete' => $isTasksInComplete,
            'tasks' => $this->paginatorService->createPaginationRequest($request, $tasksQuery),
            'searchForm' => $searchForm->createView(),
            'enableClearSearch' => !empty($searchModel->search)
        ]);
    }
}
