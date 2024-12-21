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

namespace App\Domain\Work\Form\Factory;

use App\Application\Constant\ControllerMethodConstant;
use App\Application\Exception\{
    RuntimeException,
    ConstantNotFoundException
};
use App\Application\Helper\SortFunctionHelper;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Form\{
    WorkForm,
    WorkSearchForm
};
use App\Domain\Work\Model\WorkModel;
use App\Domain\Work\Service\WorkListService;
use App\Domain\WorkDeadline\Facade\WorkDeadlineFacade;
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
        private readonly WorkListService $workListService,
        private readonly WorkDeadlineFacade $deadlineFacade,
        private readonly HashidsServiceInterface $hashidsService,
        private readonly FormFactoryInterface $formFactory
    ) {}

    public function getWorkForm(
        User $user,
        ControllerMethodConstant $type,
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

        SortFunctionHelper::usortCzechUserArray($userAuthorArray);
        SortFunctionHelper::usortCzechUserArray($userOpponentArray);
        SortFunctionHelper::usortCzechUserArray($userConsultantArray);
        SortFunctionHelper::usortCzechUserArray($userSupervisorArray);

        $workDeadLines = $this->deadlineFacade
            ->getWorkDeadlinesBySupervisor($user)
            ->toArray();

        return $this->formFactory->create(WorkSearchForm::class, $workSearchModel, [
            'authors' => $userAuthorArray,
            'opponents' => $userOpponentArray,
            'consultants' => $userConsultantArray,
            'supervisors' => $userSupervisorArray,
            'deadlines' => $workDeadLines,
            'type' => $type
        ]);
    }
}
