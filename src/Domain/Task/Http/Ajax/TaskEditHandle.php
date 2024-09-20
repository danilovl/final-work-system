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

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Helper\FormValidationMessageHelper;
use App\Application\Service\RequestService;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\EventDispatcher\TaskEventDispatcherService;
use App\Domain\Task\Factory\TaskFactory;
use App\Domain\Task\Form\TaskForm;
use App\Domain\Task\Model\TaskModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class TaskEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private TaskFactory $taskFactory,
        private FormFactoryInterface $formFactory,
        private TaskEventDispatcherService $taskEventDispatcherService
    ) {}

    public function __invoke(Request $request, Task $task): JsonResponse
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
