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

namespace App\Domain\Task\Http\Ajax;

use App\Application\Constant\{
    AjaxJsonTypeConstant,
    ControllerMethodConstant
};
use App\Application\Helper\FormValidationMessageHelper;
use App\Application\Service\RequestService;
use App\Domain\Task\DataTransferObject\Form\Factory\TaskFormFactoryData;
use App\Domain\Task\EventDispatcher\TaskEventDispatcher;
use App\Domain\Task\Factory\TaskFactory;
use App\Domain\Task\Form\Factory\TaskFormFactory;
use App\Domain\Task\Model\TaskModel;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class TaskCreateSeveralHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TaskFormFactory $taskFormFactory,
        private TaskFactory $taskFactory,
        private TaskEventDispatcher $taskEventDispatcher
    ) {}

    public function __invoke(Request $request): JsonResponse
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
                $this->taskEventDispatcher->onTaskCreate($task);
            }

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
