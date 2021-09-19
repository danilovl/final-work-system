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

use App\Form\TaskForm;
use App\Helper\FormValidationMessageHelper;
use Symfony\Component\Form\FormFactoryInterface;
use App\Entity\Task;
use App\EventDispatcher\TaskEventDispatcherService;
use App\Model\Task\Factory\TaskFactory;
use App\Model\Task\TaskModel;
use App\Constant\AjaxJsonTypeConstant;
use App\Service\RequestService;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class TaskEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private TaskFactory $taskFactory,
        private FormFactoryInterface $formFactory,
        private TaskEventDispatcherService $taskEventDispatcherService
    ) {
    }

    public function handle(Request $request, Task $task): JsonResponse
    {
        $taskModel = TaskModel::fromTask($task);
        $form = $this->formFactory
            ->create(TaskForm::class, $taskModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskFactory->flushFromModel($taskModel, $task);
            $this->taskEventDispatcherService->onTaskEdit($task);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
