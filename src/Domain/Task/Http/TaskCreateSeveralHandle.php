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

use App\Application\Constant\ControllerMethodConstant;
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Domain\Task\Bus\Command\CreateTask\CreateTaskCommand;
use App\Domain\Task\DTO\Form\Factory\TaskFormFactoryDTO;
use App\Domain\Task\Facade\TaskDeadlineFacade;
use App\Domain\Task\Form\Factory\TaskFormFactory;
use App\Domain\Task\Model\TaskModel;
use App\Domain\User\Service\UserService;
use App\Infrastructure\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class TaskCreateSeveralHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private TaskFormFactory $taskFormFactory,
        private TaskDeadlineFacade $taskDeadlineFacade,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $this->userService->getUser();

        $taskModel = new TaskModel;
        $taskModel->active = true;
        $taskModel->owner = $user;

        $taskFormFactoryDTO = new TaskFormFactoryDTO(
            type: ControllerMethodConstant::CREATE_SEVERAL,
            taskModel: $taskModel,
            options: [
                'supervisor' => $user
            ]
        );

        $form = $this->taskFormFactory
            ->getTaskForm($taskFormFactoryDTO)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($taskModel->works as $work) {
                $taskModel->work = $work;

                $command = CreateTaskCommand::create($taskModel);
                $this->commandBus->dispatch($command);
            }

            return $this->requestService->redirectToRoute('task_list');
        }

        if ($request->isXmlHttpRequest()) {
            $taskFormFactoryDTO->type = ControllerMethodConstant::CREATE_SEVERAL_AJAX;

            $form = $this->taskFormFactory->getTaskForm($taskFormFactoryDTO);
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/task/task.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.task_create'),
            'taskDeadlines' => $this->taskDeadlineFacade->getDeadlinesByOwner($user),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.create_and_close')
        ]);
    }
}
