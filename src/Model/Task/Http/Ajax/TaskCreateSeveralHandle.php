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

use App\DataTransferObject\Form\Factory\TaskFormFactoryData;
use App\Model\Task\EventDispatcher\TaskEventDispatcherService;
use App\Model\Task\Form\Factory\TaskFormFactory;
use App\Helper\FormValidationMessageHelper;
use App\Model\Task\Factory\TaskFactory;
use App\Model\Task\TaskModel;
use App\Constant\{
    AjaxJsonTypeConstant,
    ControllerMethodConstant
};
use App\Service\{
    UserService,
    RequestService
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class TaskCreateSeveralHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TaskFormFactory $taskFormFactory,
        private TaskFactory $taskFactory,
        private TaskEventDispatcherService $taskEventDispatcherService
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $user = $this->userService->getUser();

        $taskModel = new TaskModel;
        $taskModel->active = true;
        $taskModel->owner = $user;

        $taskFormFactoryData = TaskFormFactoryData::createFromArray([
            'type' => ControllerMethodConstant::CREATE_SEVERAL_AJAX,
            'taskModel' => $taskModel,
            'options' => [
                'supervisor' => $user
            ]
        ]);

        $form = $this->taskFormFactory
            ->getTaskForm($taskFormFactoryData)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($taskModel->works as $work) {
                $taskModel->work = $work;

                $task = $this->taskFactory->flushFromModel($taskModel);
                $this->taskEventDispatcherService->onTaskCreate($task);
            }

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
