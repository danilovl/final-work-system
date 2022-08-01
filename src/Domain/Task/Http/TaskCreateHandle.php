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
    TwigRenderService,
    UserService
};
use App\Domain\Task\DataTransferObject\Form\Factory\TaskFormFactoryData;
use App\Domain\Task\EventDispatcher\TaskEventDispatcherService;
use App\Domain\Task\Facade\TaskDeadlineFacade;
use App\Domain\Task\Factory\TaskFactory;
use App\Domain\Task\Form\Factory\TaskFormFactory;
use App\Domain\Task\TaskModel;
use App\Domain\Work\Entity\Work;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class TaskCreateHandle
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly UserService $userService,
        private readonly TwigRenderService $twigRenderService,
        private readonly TranslatorService $translatorService,
        private readonly TaskFormFactory $taskFormFactory,
        private readonly TaskFactory $taskFactory,
        private readonly TaskDeadlineFacade $taskDeadlineFacade,
        private readonly HashidsServiceInterface $hashidsService,
        private readonly TaskEventDispatcherService $taskEventDispatcherService
    ) {}

    public function handle(Request $request, Work $work): Response
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

        $taskFormFactoryData = TaskFormFactoryData::createFromArray([
            'type' => ControllerMethodConstant::CREATE,
            'taskModel' => $taskModel,
            'work' => $work
        ]);

        $form = $this->taskFormFactory
            ->getTaskForm($taskFormFactoryData)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $task = $this->taskFactory->flushFromModel($taskModel);
                $this->taskEventDispatcherService->onTaskCreate($task);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->requestService->redirectToRoute('work_detail', [
                    'id' => $this->hashidsService->encode($work->getId())
                ]);
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $taskFormFactoryData->type = ControllerMethodConstant::CREATE_AJAX;

            $form = $this->taskFormFactory->getTaskForm($taskFormFactoryData);
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'task/task.html.twig');

        return $this->twigRenderService->render($template, [
            'work' => $work,
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.task_create'),
            'taskDeadlines' => $this->taskDeadlineFacade->getDeadlinesByOwner($user),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.create_and_close')
        ]);
    }
}
