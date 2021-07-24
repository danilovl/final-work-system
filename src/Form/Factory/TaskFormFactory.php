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
use App\Entity\{
    Task,
    Work
};
use App\Exception\ConstantNotFoundException;
use App\Exception\RuntimeException;
use App\Model\Task\TaskModel;
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

    public function getTaskForm(
        string $type,
        TaskModel $taskModel,
        ?Task $task = null,
        ?Work $work = null
    ): FormInterface {
        $parameters = [];

        switch ($type) {
            case ControllerMethodConstant::EDIT:
            case ControllerMethodConstant::CREATE:
                break;
            case ControllerMethodConstant::CREATE_AJAX:
                if ($work === null) {
                    throw new RuntimeException('Work must not be null for create ajax');
                }

                $parameters = [
                    'action' => $this->router->generate('task_create_ajax', [
                        'id' => $this->hashidsService->encode($work->getId())
                    ]),
                    'method' => Request::METHOD_POST
                ];
                break;
            case ControllerMethodConstant::EDIT_AJAX:
                if ($work === null) {
                    throw new RuntimeException('Work must not be null for edit ajax');
                }

                $parameters = [
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

        return $this->formFactory->create(TaskForm::class, $taskModel, $parameters);
    }
}

