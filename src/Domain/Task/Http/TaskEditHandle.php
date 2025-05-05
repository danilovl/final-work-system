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

use App\Application\Constant\{
    ControllerMethodConstant,
    FlashTypeConstant
};
use App\Application\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\Task\DataTransferObject\Form\Factory\TaskFormFactoryData;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\EventDispatcher\TaskEventDispatcher;
use App\Domain\Task\Facade\TaskDeadlineFacade;
use App\Domain\Task\Factory\TaskFactory;
use App\Domain\Task\Form\Factory\TaskFormFactory;
use App\Domain\Task\Model\TaskModel;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class TaskEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private TaskFormFactory $taskFormFactory,
        private TaskFactory $taskFactory,
        private TaskDeadlineFacade $taskDeadlineFacade,
        private TaskEventDispatcher $taskEventDispatcherService
    ) {}

    public function __invoke(
        Request $request,
        Work $work,
        Task $task
    ): Response {
        $user = $this->userService->getUser();
        $taskModel = TaskModel::fromTask($task);

        $taskFormFactoryData = TaskFormFactoryData::createFromArray([
            'type' => ControllerMethodConstant::EDIT,
            'taskModel' => $taskModel,
            'task' => $task,
            'work' => $work
        ]);

        $form = $this->taskFormFactory->getTaskForm($taskFormFactoryData)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $task = $this->taskFactory
                    ->flushFromModel($taskModel, $task);

                $this->taskEventDispatcherService
                    ->onTaskEdit($task);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.save.success');

                return $this->requestService->redirectToRoute('task_list');
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.save.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.save.error');
        }

        if ($request->isXmlHttpRequest()) {
            $taskFormFactoryData->type = ControllerMethodConstant::EDIT_AJAX;

            $form = $this->taskFormFactory->getTaskForm($taskFormFactoryData);
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/task/task.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'work' => $work,
            'task' => $task,
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.task_edit'),
            'taskDeadlines' => $this->taskDeadlineFacade->getDeadlinesByOwner($user),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.update_and_close')
        ]);
    }
}
