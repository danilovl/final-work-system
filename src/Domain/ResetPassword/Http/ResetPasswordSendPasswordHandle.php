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

namespace App\Domain\ResetPassword\Http;

use App\Infrastructure\Service\{
    PaginatorService,
    TwigRenderService
};
use App\Domain\Task\Facade\TaskFacade;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class ResetPasswordSendPasswordHandle
{
    public function __construct(
        private TwigRenderService $twigRenderService,
        private UserService $userService,
        private TaskFacade $taskFacade,
        private PaginatorService $paginatorService
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $this->userService->getUser();
        $tasksQuery = $this->taskFacade
            ->queryTasksByOwner($user);

        $isTasksInComplete = $this->taskFacade
            ->isTasksCompleteByOwner($user, false);

        return $this->twigRenderService->renderToResponse('domain/task/list.html.twig', [
            'isTasksInComplete' => $isTasksInComplete,
            'tasks' => $this->paginatorService->createPaginationRequest($request, $tasksQuery)
        ]);
    }
}
