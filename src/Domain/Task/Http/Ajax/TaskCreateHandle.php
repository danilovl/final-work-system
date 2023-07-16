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
use App\Domain\Task\EventDispatcher\TaskEventDispatcherService;
use App\Domain\Task\Factory\TaskFactory;
use App\Domain\Task\Form\TaskForm;
use App\Domain\Task\TaskModel;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Entity\Work;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class TaskCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TaskFactory $taskFactory,
        private FormFactoryInterface $formFactory,
        private TaskEventDispatcherService $taskEventDispatcherService
    ) {}

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
