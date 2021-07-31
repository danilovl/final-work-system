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

namespace App\Form\Factory;

use App\Constant\ControllerMethodConstant;
use App\DataTransferObject\Form\Factory\TaskFormFactoryData;
use App\Form\TaskSeveralForm;
use App\Exception\{
    RuntimeException,
    ConstantNotFoundException
};
use Symfony\Component\Routing\RouterInterface;
use App\Form\TaskForm;
use Symfony\Component\Form\{
    FormInterface,
    FormFactoryInterface
};
use Danilovl\HashidsBundle\Services\HashidsService;
use Symfony\Component\HttpFoundation\Request;

class TaskFormFactory
{
    public function __construct(
        private RouterInterface $router,
        private HashidsService $hashidsService,
        private FormFactoryInterface $formFactory
    ) {
    }

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

