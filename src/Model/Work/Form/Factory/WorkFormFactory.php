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

namespace App\Model\Work\Form\Factory;

use App\Constant\ControllerMethodConstant;
use App\Entity\Work;
use App\Exception\ConstantNotFoundException;
use App\Model\WorkDeadline\Facade\WorkDeadlineFacade;
use App\Model\Work\Service\WorkListService;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Routing\RouterInterface;
use App\Model\Work\Form\{
    WorkForm,
    WorkSearchForm
};
use App\Helper\SortFunctionHelper;
use App\Entity\User;
use Symfony\Component\Form\{
    FormInterface,
    FormFactoryInterface
};
use App\Model\Work\WorkModel;
use App\Model\WorkSearch\WorkSearchModel;
use Symfony\Component\HttpFoundation\Request;

class WorkFormFactory
{
    public function __construct(
        private RouterInterface $router,
        private WorkListService $workListService,
        private WorkDeadlineFacade $deadlineFacade,
        private HashidsServiceInterface $hashidsService,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function getWorkForm(
        User $user,
        string $type,
        WorkModel $workModel,
        Work $work = null
    ): FormInterface {
        $parameters = [
            'user' => $user
        ];

        switch ($type) {
            case ControllerMethodConstant::EDIT:
            case ControllerMethodConstant::CREATE:
                break;
            case ControllerMethodConstant::CREATE_AJAX:
                $parameters = [
                    'action' => $this->router->generate('task_create_ajax', [
                        'id' => $this->hashidsService->encode($work->getId())
                    ]),
                    'method' => Request::METHOD_POST
                ];
                break;
            case ControllerMethodConstant::EDIT_AJAX:
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

        return $this->formFactory->create(WorkForm::class, $workModel, $parameters);
    }

    public function getSearchForm(
        User $user,
        string $type,
        WorkSearchModel $workSearchModel
    ): FormInterface {
        $userAuthorArray = $this->workListService->getUserAuthors($user, $type)->toArray();
        $userOpponentArray = $this->workListService->getUserOpponents($user, $type)->toArray();
        $userConsultantArray = $this->workListService->getUserConsultants($user, $type)->toArray();
        $userSupervisorArray = $this->workListService->getUserSupervisors($user, $type)->toArray();

        SortFunctionHelper::usortCzechArray($userAuthorArray);
        SortFunctionHelper::usortCzechArray($userOpponentArray);
        SortFunctionHelper::usortCzechArray($userConsultantArray);
        SortFunctionHelper::usortCzechArray($userSupervisorArray);

        $workDeadLines = $this->deadlineFacade
            ->getWorkDeadlinesBySupervisor($user)
            ->toArray();

        return $this->formFactory->create(WorkSearchForm::class, $workSearchModel, [
            'authors' => $userAuthorArray,
            'opponents' => $userOpponentArray,
            'consultants' => $userConsultantArray,
            'supervisors' => $userSupervisorArray,
            'deadlines' => $workDeadLines
        ]);
    }
}
