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

namespace App\Domain\Work\Form\Factory;

use App\Application\Constant\ControllerMethodConstant;
use App\Application\Exception\{
    RuntimeException,
    ConstantNotFoundException
};
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Form\{
    WorkForm,
    WorkSearchForm
};
use App\Domain\Work\Model\WorkModel;
use App\Domain\WorkSearch\Model\WorkSearchModel;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Form\{
    FormFactoryInterface,
    FormInterface
};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class WorkFormFactory
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly HashidsServiceInterface $hashidsService,
        private readonly FormFactoryInterface $formFactory
    ) {}

    public function getWorkForm(
        User $user,
        ControllerMethodConstant $type,
        WorkModel $workModel,
        ?Work $work = null
    ): FormInterface {
        $parameters = [
            'user' => $user
        ];

        switch ($type) {
            case ControllerMethodConstant::EDIT:
            case ControllerMethodConstant::CREATE:
                break;
            case ControllerMethodConstant::CREATE_AJAX:
                if ($work === null) {
                    throw new RuntimeException('Work is null.');
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
                    throw new RuntimeException('Work is null.');
                }

                $parameters = array_merge($parameters, [
                    'action' => $this->router->generate('work_edit_ajax', [
                        'id' => $this->hashidsService->encode($work->getId())
                    ]),
                    'method' => Request::METHOD_POST
                ]);

                break;
            default:
                throw new ConstantNotFoundException('Controller method type constant not found');
        }

        return $this->formFactory->create(type: WorkForm::class, data: $workModel, options: $parameters);
    }

    public function getSearchForm(
        string $type,
        WorkSearchModel $workSearchModel
    ): FormInterface {
        return $this->formFactory->create(WorkSearchForm::class, $workSearchModel, [
            'type' => $type
        ]);
    }
}
