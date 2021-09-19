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

namespace App\Model\Task\Http\Ajax;

use App\Entity\Work;
use App\EventDispatcher\TaskEventDispatcherService;
use App\Form\TaskForm;
use App\Helper\FormValidationMessageHelper;
use App\Model\Task\Factory\TaskFactory;
use App\Model\Task\TaskModel;
use App\Constant\AjaxJsonTypeConstant;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\{
    UserService,
    RequestService,
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class TaskCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TaskFactory $taskFactory,
        private FormFactoryInterface $formFactory,
        private TaskEventDispatcherService $taskEventDispatcherService
    ) {
    }

    public function handle(Request $request, Work $work): JsonResponse
    {
        $taskModel = new TaskModel;
        $taskModel->active = true;
        $taskModel->work = $work;
        $taskModel->owner = $this->userService->getUser();

        $form = $this->formFactory
            ->create(TaskForm::class, $taskModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task = $this->taskFactory->flushFromModel($taskModel);
            $this->taskEventDispatcherService->onTaskCreate($task);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
