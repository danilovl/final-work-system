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
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Domain\Task\Bus\Command\CreateTask\CreateTaskCommand;
use App\Domain\Task\DTO\Form\Factory\TaskFormFactoryDTO;
use App\Domain\Task\Form\Factory\TaskFormFactory;
use App\Domain\Task\Model\TaskModel;
use App\Domain\User\Service\UserService;
use App\Infrastructure\Service\RequestService;
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
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->userService->getUser();

        $taskModel = new TaskModel;
        $taskModel->active = true;
        $taskModel->owner = $user;

        $taskFormFactoryDTO = new TaskFormFactoryDTO(
            type: ControllerMethodConstant::CREATE_SEVERAL_AJAX,
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

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
