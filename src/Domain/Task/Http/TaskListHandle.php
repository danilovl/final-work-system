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
use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Application\Model\SearchModel;
use App\Domain\Task\Bus\Query\TaskList\{
    GetTaskListQuery,
    GetTaskListQueryResult
};
use App\Application\Service\TwigRenderService;
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
        private FormFactoryInterface $formFactory,
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $this->userService->getUser();

        $searchModel = new SearchModel;
        $searchForm = $this->formFactory
            ->create(SimpleSearchForm::class, $searchModel)
            ->handleRequest($request);

        $query = GetTaskListQuery::create(
            request: $request,
            user: $user,
            search: $searchForm->isSubmitted() && $searchForm->isValid() ? $searchModel->search : null
        );

        /** @var GetTaskListQueryResult $result */
        $result = $this->queryBus->handleResult($query);

        return $this->twigRenderService->renderToResponse('domain/task/list.html.twig', [
            'isTasksInComplete' => $result->isTasksInComplete,
            'tasks' => $result->tasks,
            'searchForm' => $searchForm->createView(),
            'enableClearSearch' => !empty($searchModel->search)
        ]);
    }
}
