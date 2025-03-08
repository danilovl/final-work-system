<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Domain\Task\Form\Factory;

use App\Application\Constant\ControllerMethodConstant;
use App\Application\Exception\{
    ConstantNotFoundException,
    RuntimeException
};
use App\Domain\Task\DataTransferObject\Form\Factory\TaskFormFactoryData;
use App\Domain\Task\Form\{
    TaskForm,
    TaskSeveralForm
};
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Form\{
    FormFactoryInterface,
    FormInterface
};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class TaskFormFactory
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly HashidsServiceInterface $hashidsService,
        private readonly FormFactoryInterface $formFactory
    ) {}

    public function getTaskForm(TaskFormFactoryData $taskFormFactoryData): FormInterface
    {
        $formTypeClass = TaskForm::class;
        $taskModel = $taskFormFactoryData->taskModel;
        $work = $taskFormFactoryData->work;
        $task = $taskFormFactoryData->task;
        $options = $taskFormFactoryData->options;
        $typeOptions = [];

        switch ($taskFormFactoryData->type) {
            case ControllerMethodConstant::EDIT:
            case ControllerMethodConstant::CREATE:
                break;
            case ControllerMethodConstant::CREATE_AJAX:
                if ($work === null) {
                    throw new RuntimeException('Work must not be null for create ajax');
                }

                $typeOptions = [
                    'action' => $this->router->generate('task_create_ajax', [
                        'id' => $this->hashidsService->encode($work->getId())
                    ]),
                    'method' => Request::METHOD_POST
                ];

                break;
            case ControllerMethodConstant::CREATE_SEVERAL:
                $formTypeClass = TaskSeveralForm::class;

                break;
            case ControllerMethodConstant::CREATE_SEVERAL_AJAX:
                $formTypeClass = TaskSeveralForm::class;

                $typeOptions = [
                    'action' => $this->router->generate('task_create_several_ajax'),
                    'method' => Request::METHOD_POST
                ];

                break;
            case ControllerMethodConstant::EDIT_AJAX:
                if ($work === null) {
                    throw new RuntimeException('Work must not be null for edit ajax');
                }

                $typeOptions = [
                    'action' => $this->router->generate('task_edit_ajax', [
                        'id_task' => $this->hashidsService->encode($task->getId()),
                        'id_work' => $this->hashidsService->encode($work->getId())
                    ]),
                    'method' => Request::METHOD_POST
                ];

                break;
            default:
                throw new ConstantNotFoundException('Controller method type constant not found');
        }

        $options = array_merge($options, $typeOptions);

        return $this->formFactory->create($formTypeClass, $taskModel, $options);
    }
}
