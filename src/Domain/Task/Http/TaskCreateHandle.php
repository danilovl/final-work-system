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
use App\Domain\Work\Entity\Work;
use App\Infrastructure\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService
};
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class TaskCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private TaskFormFactory $taskFormFactory,
        private TaskDeadlineFacade $taskDeadlineFacade,
        private HashidsServiceInterface $hashidsService,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request, Work $work): Response
    {
        $user = $this->userService->getUser();
        $taskName = $request->query->get('taskName');

        $taskModel = new TaskModel;
        $taskModel->active = true;
        $taskModel->work = $work;
        $taskModel->owner = $user;

        if (!empty($taskName)) {
            $taskModel->name = $taskName;
        }

        $taskFormFactoryDTO = new TaskFormFactoryDTO(
            type: ControllerMethodConstant::CREATE,
            taskModel: $taskModel,
            work: $work
        );

        $form = $this->taskFormFactory
            ->getTaskForm($taskFormFactoryDTO)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = CreateTaskCommand::create($taskModel);
            $this->commandBus->dispatch($command);

            return $this->requestService->redirectToRoute('work_detail', [
                'id' => $this->hashidsService->encode($work->getId())
            ]);
        }

        if ($request->isXmlHttpRequest()) {
            $taskFormFactoryDTO->type = ControllerMethodConstant::CREATE_AJAX;

            $form = $this->taskFormFactory->getTaskForm($taskFormFactoryDTO);
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/task/task.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'work' => $work,
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.task_create'),
            'taskDeadlines' => $this->taskDeadlineFacade->getDeadlinesByOwner($user),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.create_and_close')
        ]);
    }
}
